<?php

namespace App\Tests\Repository;

use App\Entity\PersonnelDoc;
use App\Repository\PersonnelDocRepository;
use DateTimeImmutable;

class PersonnelDocRepositoryTest extends RepositoryTestCase
{
    private PersonnelDocRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->em->getRepository(PersonnelDoc::class);
    }

    public function testRepositoryIsInstantiable(): void
    {
        $this->assertInstanceOf(PersonnelDocRepository::class, $this->repository);
    }

    public function testPersistAndFindPersonnelDoc(): void
    {
        $doc = new PersonnelDoc();
        $doc->setId(1001)
            ->setCodagt('AGT001')
            ->setIDDOC(42)
            ->setDocRef('REF-TEST')
            ->setDtedeb(new DateTimeImmutable('2024-01-01'));

        $this->em->persist($doc);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->find(1001);

        $this->assertNotNull($found);
        $this->assertSame('AGT001', $found->getCodagt());
        $this->assertSame(42, $found->getIDDOC());
        $this->assertSame('REF-TEST', $found->getDocRef());
    }

    public function testFindByCodagt(): void
    {
        $results = $this->repository->findBy(['codagt' => 'AGT001']);

        $this->assertIsArray($results);
    }
}
