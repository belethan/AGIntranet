<?php

namespace App\Entity;

use App\Repository\PersonnelDocRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonnelDocRepository::class)]
class PersonnelDoc
{
    // ID managé à la main (Oracle sequence via webservice)
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    private ?string $codagt = null;

    #[ORM\Column]
    private ?int $IDDOC = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $doc_ref = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dtedeb = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $flag_actif;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dtefin = null;

    #[ORM\Column]

    private ?DateTimeImmutable $dtecreation = null;
    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dtemodif = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $opecreation = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $opemodif = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $libtype = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $flag_ligne;

    #[ORM\Column(name: 'external_hash', length: 255, nullable: true)]
    private ?string $externalHash = null;



    public function __construct()
    {
        $this->dtecreation = new DateTimeImmutable();
        $this->flag_actif = true;
        $this->flag_ligne = 0; // Valeur par défaut si nécessaire
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCodagt(): ?string
    {
        return $this->codagt;
    }

    public function setCodagt(string $codagt): static
    {
        $this->codagt = $codagt;

        return $this;
    }

    public function getIDDOC(): ?int
    {
        return $this->IDDOC;
    }

    public function setIDDOC(int $IDDOC): static
    {
        $this->IDDOC = $IDDOC;

        return $this;
    }

    public function getDocRef(): ?string
    {
        return $this->doc_ref;
    }

    public function setDocRef(?string $doc_ref): static
    {
        $this->doc_ref = $doc_ref;

        return $this;
    }

    public function getDtedeb(): ?DateTimeImmutable
    {
        return $this->dtedeb;
    }

    public function setDtedeb(?DateTimeImmutable $dtedeb): static
    {
        $this->dtedeb = $dtedeb;

        return $this;
    }

    public function getFlagActif(): ?int
    {
        return $this->flag_actif;
    }

    public function setFlagActif(int $flag_actif): static
    {
        $this->flag_actif = $flag_actif;

        return $this;
    }

    public function getDtefin(): ?DateTimeImmutable
    {
        return $this->dtefin;
    }

    public function setDtefin(?DateTimeImmutable $dtefin): static
    {
        $this->dtefin = $dtefin;

        return $this;
    }

    public function getDtecreation(): ?DateTimeImmutable
    {
        return $this->dtecreation;
    }

    public function setDtecreation(DateTimeImmutable $dtecreation): static
    {
        $this->dtecreation = $dtecreation;

        return $this;
    }

    public function getDtemodif(): ?DateTimeImmutable
    {
        return $this->dtemodif;
    }

    public function setDtemodif(?DateTimeImmutable $dtemodif): static
    {
        $this->dtemodif = $dtemodif;

        return $this;
    }

    public function getOpecreation(): ?string
    {
        return $this->opecreation;
    }

    public function setOpecreation(?string $opecreation): static
    {
        $this->opecreation = $opecreation;

        return $this;
    }

    public function getOpemodif(): ?string
    {
        return $this->opemodif;
    }

    public function setOpemodif(?string $opemodif): static
    {
        $this->opemodif = $opemodif;

        return $this;
    }

    public function getLibtype(): ?string
    {
        return $this->libtype;
    }

    public function setLibtype(?string $libtype): static
    {
        $this->libtype = $libtype;

        return $this;
    }

    public function getFlagLigne(): ?int
    {
        return $this->flag_ligne;
    }

    public function setFlagLigne(?int $flag_ligne): static
    {
        $this->flag_ligne = $flag_ligne;

        return $this;
    }

    public function getExternalHash(): ?string
    {
        return $this->externalHash;
    }

    public function setExternalHash(?string $externalHash): self
    {
        $this->externalHash = $externalHash;

        return $this;
    }

    public function computeExternalHash(): string
    {
        $data = [
            'codagt'      => $this->codagt,
            'IDDOC'       => $this->IDDOC,
            'doc_ref'     => $this->doc_ref,
            'dtedeb'      => $this->dtedeb?->format('Y-m-d'),
            'dtefin'      => $this->dtefin?->format('Y-m-d'),
            'flag_actif'  => $this->flag_actif,
            'libtype'     => $this->libtype,
            'flag_ligne'  => $this->flag_ligne,
        ];

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }



}
