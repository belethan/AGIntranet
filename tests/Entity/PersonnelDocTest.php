<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\PersonnelDoc;
use PHPUnit\Framework\TestCase;

class PersonnelDocTest extends TestCase
{
    public function testPersonnelDocGettersAndSetters(): void
    {
        $doc = new PersonnelDoc();

        $dateDebut = new \DateTimeImmutable('2025-01-01');
        $dateFin   = new \DateTimeImmutable('2025-12-31');
        $dateModif = new \DateTimeImmutable('2025-02-01');

        $doc->setIDDOC(123);
        $doc->setCodagt('AG123');
        $doc->setDocRef('DOC-REF-001');
        $doc->setDtedeb($dateDebut);
        $doc->setDtefin($dateFin);
        $doc->setDtemodif($dateModif);
        $doc->setFlagActif(1);
        $doc->setOpecreation('SYSTEM');
        $doc->setExternalHash('HASH123');

        $this->assertSame(123, $doc->getIDDOC());
        $this->assertSame('AG123', $doc->getCodagt());
        $this->assertSame('DOC-REF-001', $doc->getDocRef());
        $this->assertSame($dateDebut, $doc->getDtedeb());
        $this->assertSame($dateFin, $doc->getDtefin());
        $this->assertSame($dateModif, $doc->getDtemodif());
        $this->assertSame(1, $doc->getFlagActif());
        $this->assertSame('SYSTEM', $doc->getOpecreation());
        $this->assertSame('HASH123', $doc->getExternalHash());
    }

    public function testPersonnelDocDefaults(): void
    {
        $doc = new PersonnelDoc();

        $this->assertNull($doc->getIDDOC());
        $this->assertNull($doc->getCodagt());
        $this->assertNull($doc->getDocRef());
        $this->assertNull($doc->getDtedeb());
        $this->assertNull($doc->getDtefin());

        // dtecreation est initialisée automatiquement par l'entité
        $this->assertInstanceOf(
            \DateTimeImmutable::class,
            $doc->getDtecreation()
        );

        $this->assertNull($doc->getDtemodif());
        $this->assertNull($doc->getOpecreation());
        $this->assertNull($doc->getExternalHash());
    }
}
