<?php

namespace App\Tests\Unit\Entity;

use App\Entity\PersonnelDoc;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PersonnelDocTest extends TestCase
{
    public function testConstructorInitializesDefaults(): void
    {
        $doc = new PersonnelDoc();

        $this->assertInstanceOf(DateTimeImmutable::class, $doc->getDtecreation());
        $this->assertSame(1, $doc->getFlagActif());
        $this->assertSame(0, $doc->getFlagLigne());
    }

    public function testId(): void
    {
        $doc = new PersonnelDoc();
        $doc->setId(100);

        $this->assertSame(100, $doc->getId());
    }

    public function testCodagt(): void
    {
        $doc = new PersonnelDoc();
        $doc->setCodagt('AGT001');

        $this->assertSame('AGT001', $doc->getCodagt());
    }

    public function testIdDoc(): void
    {
        $doc = new PersonnelDoc();
        $doc->setIDDOC(42);

        $this->assertSame(42, $doc->getIDDOC());
    }

    public function testDocRef(): void
    {
        $doc = new PersonnelDoc();
        $doc->setDocRef('REF-123');

        $this->assertSame('REF-123', $doc->getDocRef());
    }

    public function testDates(): void
    {
        $start = new DateTimeImmutable('2024-01-01');
        $end   = new DateTimeImmutable('2024-12-31');

        $doc = new PersonnelDoc();
        $doc->setDtedeb($start)
            ->setDtefin($end);

        $this->assertSame($start, $doc->getDtedeb());
        $this->assertSame($end, $doc->getDtefin());
    }

    public function testFlags(): void
    {
        $doc = new PersonnelDoc();

        $doc->setFlagActif(0);
        $doc->setFlagLigne(1);

        $this->assertSame(0, $doc->getFlagActif());
        $this->assertSame(1, $doc->getFlagLigne());
    }

    public function testOperators(): void
    {
        $doc = new PersonnelDoc();

        $doc->setOpecreation('SYSTEM')
            ->setOpemodif('ADMIN');

        $this->assertSame('SYSTEM', $doc->getOpecreation());
        $this->assertSame('ADMIN', $doc->getOpemodif());
    }

    public function testLibtype(): void
    {
        $doc = new PersonnelDoc();
        $doc->setLibtype('ATTESTATION');

        $this->assertSame('ATTESTATION', $doc->getLibtype());
    }
}

