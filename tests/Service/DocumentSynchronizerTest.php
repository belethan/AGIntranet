<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PersonnelDoc;
use App\Service\DocumentSynchronizer;
use App\Service\DocumentWebserviceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DocumentSynchronizerTest extends TestCase
{
    private EntityManagerInterface $em;
    private EntityRepository $repository;
    private DocumentWebserviceInterface $ws;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);
        $this->ws = $this->createMock(DocumentWebserviceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->em
            ->method('getRepository')
            ->with(PersonnelDoc::class)
            ->willReturn($this->repository);
    }

    public function testSyncCreatesNewDocument(): void
    {
        $codagt = 'AG123';

        $this->ws
            ->method('fetchDocuments')
            ->willReturn([
                ['ID' => 10, 'LIBELLE' => 'Contrat'],
            ]);

        $this->repository
            ->method('findBy')
            ->willReturn([]);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);

        $sync = new DocumentSynchronizer(
            $this->em,
            $serializer,
            $this->ws,
            $this->logger
        );

        $result = $sync->syncForUser($codagt, false);

        $this->assertSame(1, $result->getCreated());
        $this->assertSame(0, $result->getUpdated());
        $this->assertSame(0, $result->getIgnored());
    }

    public function testSyncDoesNothingIfHashIsIdentical(): void
    {
        $codagt = 'AG123';

        $row = ['ID' => 10, 'LIBELLE' => 'Contrat'];
        ksort($row);
        $hash = hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));

        $doc = new PersonnelDoc();
        $doc
            ->setIDDOC(10)
            ->setCodagt($codagt)
            ->setExternalHash($hash);

        $this->ws
            ->method('fetchDocuments')
            ->willReturn([$row]);

        $this->repository
            ->method('findBy')
            ->willReturn([$doc]);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);

        $sync = new DocumentSynchronizer(
            $this->em,
            $serializer,
            $this->ws,
            $this->logger
        );

        $result = $sync->syncForUser($codagt, false);

        $this->assertSame(1, $result->getIgnored());
    }

    public function testSyncUpdatesExistingDocumentIfHashDiffers(): void
    {
        $codagt = 'AG123';

        $doc = new PersonnelDoc();
        $doc
            ->setIDDOC(10)
            ->setCodagt($codagt)
            ->setExternalHash('OLD');

        $this->ws
            ->method('fetchDocuments')
            ->willReturn([
                ['ID' => 10, 'LIBELLE' => 'Contrat MAJ'],
            ]);

        $this->repository
            ->method('findBy')
            ->willReturn([$doc]);

        $this->em->expects($this->once())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);

        $sync = new DocumentSynchronizer(
            $this->em,
            $serializer,
            $this->ws,
            $this->logger
        );

        $result = $sync->syncForUser($codagt, false);

        $this->assertSame(1, $result->getUpdated());
        $this->assertNotSame('OLD', $doc->getExternalHash());
    }
}
