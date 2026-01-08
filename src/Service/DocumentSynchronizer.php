<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PersonnelDoc;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

final class DocumentSynchronizer
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly DocumentWebserviceInterface $documentWebservice,
        private readonly LoggerInterface $agducLogger // canal "agduc"
    ) {}

    public function syncForUser(string $codagt, bool $dryRun = false): DocumentSyncResult
    {
        $this->agducLogger->info('Début synchronisation documents', [
            'codagt' => $codagt,
            'dry_run' => $dryRun,
        ]);

        $result = new DocumentSyncResult();

        // ============================
        // WS AGDUC
        // ============================
        $wsDocs = $this->documentWebservice->fetchDocuments($codagt);

        $this->agducLogger->info('Documents WS récupérés', [
            'codagt' => $codagt,
            'count' => count($wsDocs),
        ]);

        // ============================
        // DB
        // ============================
        $repo   = $this->em->getRepository(PersonnelDoc::class);
        $dbDocs = $repo->findBy(['codagt' => $codagt]);

        $this->agducLogger->debug('Documents DB existants', [
            'codagt' => $codagt,
            'count' => count($dbDocs),
        ]);

        // Index DB par IDDOC Oracle
        $dbIndex = [];
        foreach ($dbDocs as $doc) {
            if ($doc->getIDDOC() !== null) {
                $dbIndex[$doc->getIDDOC()] = $doc;
            }
        }

        foreach ($wsDocs as $row) {
            $result->incrementTotal();

            $iddoc = $this->extractIddoc($row);
            if ($iddoc === null) {
                $result->incrementIgnored();

                $this->agducLogger->warning('Document WS ignoré (IDDOC manquant)', [
                    'codagt' => $codagt,
                    'row' => $row,
                ]);
                continue;
            }

            $externalHash = $this->computeRowHash($row);

            /**
             * ============================
             * DOCUMENT EXISTANT
             * ============================
             */
            if (isset($dbIndex[$iddoc])) {
                $doc = $dbIndex[$iddoc];

                if ($doc->getExternalHash() === $externalHash) {
                    $result->incrementIgnored();

                    $this->agducLogger->debug('Document inchangé', [
                        'codagt' => $codagt,
                        'iddoc' => $iddoc,
                    ]);
                } else {
                    $result->incrementUpdated();

                    $this->agducLogger->info('Document mis à jour', [
                        'codagt' => $codagt,
                        'iddoc' => $iddoc,
                        'dry_run' => $dryRun,
                    ]);

                    if (!$dryRun) {
                        $this->hydrateDocument($doc, $row, $codagt);
                        $doc->setExternalHash($externalHash);
                    }
                }

                unset($dbIndex[$iddoc]);
                continue;
            }

            /**
             * ============================
             * NOUVEAU DOCUMENT
             * ============================
             */
            $result->incrementCreated();

            $this->agducLogger->info('Nouveau document détecté', [
                'codagt' => $codagt,
                'iddoc' => $iddoc,
                'dry_run' => $dryRun,
            ]);

            if (!$dryRun) {
                $doc = new PersonnelDoc();

                // ⚠️ IMPORTANT : setters AVANT persist
                $doc->setCodagt($codagt);
                $doc->setIDDOC($iddoc);

                $this->hydrateDocument($doc, $row, $codagt);
                $doc->setExternalHash($externalHash);

                $this->em->persist($doc);
            }
        }

        if (!$dryRun) {
            $this->em->flush();
        }

        $this->agducLogger->info('Fin synchronisation documents', [
            'codagt' => $codagt,
            'dry_run' => $dryRun,
            'total' => $result->getTotal(),
            'created' => $result->getCreated(),
            'updated' => $result->getUpdated(),
            'ignored' => $result->getIgnored(),
        ]);

        return $result;
    }

    /**
     * =====================================================
     * Hydratation SAFE
     * =====================================================
     */
    private function hydrateDocument(PersonnelDoc $doc, array $row, string $codagt): void
    {
        foreach ($row as $key => $value) {
            if ($value === '' || $value === '0000-00-00') {
                $row[$key] = null;
            }
        }

        $data = [
            'doc_ref'    => $row['DOC_REF']   ?? null,
            'libtype'    => $row['LIBTYPE']   ?? null,
            'flag_actif' => isset($row['FLAG_ACTIF']) ? (int) $row['FLAG_ACTIF'] : 1,
            'flag_ligne' => isset($row['FLAG_LIGNE']) ? (int) $row['FLAG_LIGNE'] : 0,
        ];

        $this->serializer->denormalize(
            $data,
            PersonnelDoc::class,
            'array',
            ['object_to_populate' => $doc]
        );

        $doc->setDtedeb($this->parseDate($row['DTEDEB'] ?? null));
        $doc->setDtefin($this->parseDate($row['DTEFIN'] ?? null));

        $doc->setCodagt($codagt);
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if (!$value) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function extractIddoc(array $row): ?int
    {
        foreach (['ID', 'IDDOC', 'iddoc', 'id'] as $key) {
            if (!empty($row[$key])) {
                $id = (int) $row[$key];
                return $id > 0 ? $id : null;
            }
        }

        return null;
    }

    private function computeRowHash(array $row): string
    {
        ksort($row);
        return hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));
    }
}
