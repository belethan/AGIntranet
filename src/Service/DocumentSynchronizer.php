<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PersonnelDoc;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DocumentSynchronizer
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly DocumentWebservice $documentWebservice
    ) {}

    /**
     * Synchronise les documents Oracle vers MySQL pour un agent donné.
     *
     * Règle clé :
     *  - WS.ID  => PersonnelDoc::IDDOC (clé métier)
     *  - Doctrine gère seul PersonnelDoc::id (clé technique)
     *
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function sync(string $codagt): void
    {
        // 1) Documents Oracle (source de vérité)
        $wsDocs = $this->documentWebservice->fetchDocuments($codagt);

        // 2) Documents existants en base pour l’agent
        $repo   = $this->em->getRepository(PersonnelDoc::class);
        $dbDocs = $repo->findBy(['codagt' => $codagt]);

        /**
         * Index MySQL par IDDOC (clé métier)
         * [
         *   IDDOC => PersonnelDoc
         * ]
         */
        $dbIndex = [];
        foreach ($dbDocs as $doc) {
            if ($doc->getIDDOC() !== null) {
                $dbIndex[$doc->getIDDOC()] = $doc;
            }
        }

        // 3) Synchronisation WS → MySQL
        foreach ($wsDocs as $row) {

            // IDDOC Oracle
            $iddoc = isset($row['ID']) && $row['ID'] !== ''
                ? (int) $row['ID']
                : null;

            // Hash externe (état fonctionnel du document)
            $externalHash = hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));

            /**
             * === CAS 1 : Document existant → mise à jour éventuelle
             */
            if ($iddoc !== null && isset($dbIndex[$iddoc])) {

                $doc = $dbIndex[$iddoc];

                // Hash identique → aucune action
                if ($doc->getExternalHash() === $externalHash) {
                    unset($dbIndex[$iddoc]);
                    continue;
                }

                // Mise à jour par dénormalisation
                $this->serializer->denormalize(
                    $row,
                    PersonnelDoc::class,
                    'array',
                    ['object_to_populate' => $doc]
                );

                $doc->setExternalHash($externalHash);

                unset($dbIndex[$iddoc]);
                continue;
            }

            /**
             * === CAS 2 : Nouveau document Oracle
             */
            $doc = new PersonnelDoc();

            // Si IDDOC absent → génération via WS
            if ($iddoc === null) {
                $iddoc = $this->documentWebservice->fetchNewDocId();
            }

            $doc->setIDDOC($iddoc);
            $doc->setCodagt($codagt);

            // Hydratation
            $this->serializer->denormalize(
                $row,
                PersonnelDoc::class,
                'array',
                ['object_to_populate' => $doc]
            );

            $doc->setExternalHash($externalHash);

            $this->em->persist($doc);
        }

        /**
         * 4) Suppression :
         * Tous les documents restants dans $dbIndex
         * sont absents du WS → suppression MySQL
         */
        foreach ($dbIndex as $docToRemove) {
            $this->em->remove($docToRemove);
        }

        // 5) Flush unique
        $this->em->flush();
    }
}
