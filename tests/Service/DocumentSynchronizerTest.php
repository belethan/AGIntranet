<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PersonnelDoc;
use App\Service\DocumentSynchronizer;
use App\Service\DocumentWebservice;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DocumentSynchronizerTest extends TestCase
{
    private EntityManagerInterface $em;
    private EntityRepository $repository;
    private DocumentWebservice $ws;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);
        $this->ws = $this->createMock(DocumentWebservice::class);

        $this->em->method('getRepository')
            ->with(PersonnelDoc::class)
            ->willReturn($this->repository);
    }

    public function testSyncCreatesNewDocument(): void
    {
        $codagt = 'AG123';

        $wsDocs = [
            ['ID' => 10, 'LIBELLE' => 'Contrat'],
        ];

        $this->ws->method('fetchDocuments')->with($codagt)->willReturn($wsDocs);
        $this->repository->method('findBy')->willReturn([]);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);
        $sync = new DocumentSynchronizer($this->em, $serializer, $this->ws);

        $sync->sync($codagt);
    }

    public function testSyncDoesNothingIfHashIsIdentical(): void
    {
        $codagt = 'AG123';

        $row = ['ID' => 10, 'LIBELLE' => 'Contrat'];
        $hash = hash('sha256', json_encode($row));

        $doc = new PersonnelDoc();
        $doc->setId(10);
        $doc->setExternalHash($hash);

        $this->ws->method('fetchDocuments')->willReturn([$row]);
        $this->repository->method('findBy')->willReturn([$doc]);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->once())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);
        $sync = new DocumentSynchronizer($this->em, $serializer, $this->ws);

        $sync->sync($codagt);
    }

    public function testSyncUpdatesExistingDocumentIfHashDiffers(): void
    {
        $codagt = 'AG123';

        $row = ['ID' => 10, 'LIBELLE' => 'Contrat MAJ'];

        $doc = new PersonnelDoc();
        $doc->setId(10);
        $doc->setExternalHash('OLD_HASH');

        $this->ws->method('fetchDocuments')->willReturn([$row]);
        $this->repository->method('findBy')->willReturn([$doc]);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);
        $sync = new DocumentSynchronizer($this->em, $serializer, $this->ws);

        $sync->sync($codagt);

        $this->assertNotSame('OLD_HASH', $doc->getExternalHash());
    }

    public function testSyncRemovesDocumentsMissingFromWs(): void
    {
        $codagt = 'AG123';

        $doc = new PersonnelDoc();
        $doc->setId(99);

        $this->ws->method('fetchDocuments')->willReturn([]);
        $this->repository->method('findBy')->willReturn([$doc]);

        $this->em->expects($this->once())->method('remove')->with($doc);
        $this->em->expects($this->once())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);
        $sync = new DocumentSynchronizer($this->em, $serializer, $this->ws);

        $sync->sync($codagt);
    }
}
