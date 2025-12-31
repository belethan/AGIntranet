<?php

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
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function sync(string $codagt): void
    {
        // 1) Documents depuis Oracle
        $wsDocs = $this->documentWebservice->fetchDocuments($codagt);

        // 2) Documents déjà en base pour cet agent
        $repo   = $this->em->getRepository(PersonnelDoc::class);
        $dbDocs = $repo->findBy(['codagt' => $codagt]);

        // Index MySQL → par ID (clé technique = ID)
        $dbIndex = [];
        foreach ($dbDocs as $doc) {
            $dbIndex[$doc->getId()] = $doc;
        }

        // 3) Synchronisation WS → MySQL
        foreach ($wsDocs as $row) {

            // ID depuis Oracle
            $id = isset($row['ID']) && $row['ID'] !== '' ? (int)$row['ID'] : null;

            // hash Oracle
            $externalHash = hash('sha256', json_encode($row));

            // === CAS 1 : Document déjà existant (mise à jour potentielle)
            if ($id !== null && isset($dbIndex[$id])) {

                $doc = $dbIndex[$id];

                // Hash identique → rien
                if ($doc->getExternalHash() === $externalHash) {
                    unset($dbIndex[$id]);
                    continue;
                }

                // Mise à jour via dénormalisation directe
                $this->serializer->denormalize(
                    $row,
                    PersonnelDoc::class,
                    'array',
                    ['object_to_populate' => $doc]
                );

                $doc->setExternalHash($externalHash);
                unset($dbIndex[$id]);
                continue;
            }

            // === CAS 2 : Nouveau document Oracle (pas d'ID fourni ou inconnue en BDD)
            $doc = new PersonnelDoc();

            // Si ID absent dans WS → appel DOCKEY
            if ($id === null) {
                $id = $this->documentWebservice->fetchNewDocId();
            }

            $doc->setId($id);

            // Dénormalisation automatique
            $this->serializer->denormalize(
                $row,
                PersonnelDoc::class,
                'array',
                ['object_to_populate' => $doc]
            );

            $doc->setCodagt($codagt); // important
            $doc->setExternalHash($externalHash);

            $this->em->persist($doc);
        }

        // 4) Suppression des documents présents en BDD mais absents du WS
        foreach ($dbIndex as $docToRemove) {
            $this->em->remove($docToRemove);
        }

        // 5) Flush final
        $this->em->flush();
    }

}

