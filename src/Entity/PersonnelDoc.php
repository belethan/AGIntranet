<?php

namespace App\Entity;

use App\Repository\PersonnelDocRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonnelDocRepository::class)]
#[ORM\Table(name: 'personnel_doc')]
#[ORM\Index(columns: ['codagt'], name: 'idx_personnel_doc_codagt')]
#[ORM\Index(columns: ['IDDOC'], name: 'idx_personnel_doc_iddoc')]
class PersonnelDoc
{
    /**
     * ID technique MySQL
     * ⚠️ FOURNI PAR ORACLE (via WS) → PAS auto-généré
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * Code agent AGDUC (clé métier User)
     */
    #[ORM\Column(length: 8)]
    private ?string $codagt = null;

    /**
     * ID document Oracle (clé métier stable)
     */
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $IDDOC = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $doc_ref = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dtedeb = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $flag_actif = 1;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dtefin = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $dtecreation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $dtemodif = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $opecreation = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $opemodif = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $libtype = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $flag_ligne = 0;

    #[ORM\Column(name: 'external_hash', length: 255, nullable: true)]
    private ?string $externalHash = null;

    public function __construct()
    {
        $this->dtecreation = new DateTimeImmutable();
    }

    // =========================================================
    // GETTERS / SETTERS
    // =========================================================

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * ⚠️ ID fourni par Oracle / WS
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getCodagt(): ?string
    {
        return $this->codagt;
    }

    public function setCodagt(string $codagt): self
    {
        $this->codagt = $codagt;
        return $this;
    }

    public function getIDDOC(): ?int
    {
        return $this->IDDOC;
    }

    public function setIDDOC(int $IDDOC): self
    {
        $this->IDDOC = $IDDOC;
        return $this;
    }

    public function getDocRef(): ?string
    {
        return $this->doc_ref;
    }

    public function setDocRef(?string $doc_ref): self
    {
        $this->doc_ref = $doc_ref;
        return $this;
    }

    public function getDtedeb(): ?DateTimeImmutable
    {
        return $this->dtedeb;
    }

    public function setDtedeb(?DateTimeImmutable $dtedeb): self
    {
        $this->dtedeb = $dtedeb;
        return $this;
    }

    public function getFlagActif(): int
    {
        return $this->flag_actif;
    }

    public function setFlagActif(int $flag_actif): self
    {
        $this->flag_actif = $flag_actif;
        return $this;
    }

    public function getDtefin(): ?DateTimeImmutable
    {
        return $this->dtefin;
    }

    public function setDtefin(?DateTimeImmutable $dtefin): self
    {
        $this->dtefin = $dtefin;
        return $this;
    }

    public function getDtecreation(): DateTimeImmutable
    {
        return $this->dtecreation;
    }

    public function getDtemodif(): ?DateTimeImmutable
    {
        return $this->dtemodif;
    }

    public function setDtemodif(?DateTimeImmutable $dtemodif): self
    {
        $this->dtemodif = $dtemodif;
        return $this;
    }

    public function getOpecreation(): ?string
    {
        return $this->opecreation;
    }

    public function setOpecreation(?string $opecreation): self
    {
        $this->opecreation = $opecreation;
        return $this;
    }

    public function getOpemodif(): ?string
    {
        return $this->opemodif;
    }

    public function setOpemodif(?string $opemodif): self
    {
        $this->opemodif = $opemodif;
        return $this;
    }

    public function getLibtype(): ?string
    {
        return $this->libtype;
    }

    public function setLibtype(?string $libtype): self
    {
        $this->libtype = $libtype;
        return $this;
    }

    public function getFlagLigne(): int
    {
        return $this->flag_ligne;
    }

    public function setFlagLigne(int $flag_ligne): self
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

    /**
     * Hash fonctionnel du document (comparaison WS / DB)
     */
    public function computeExternalHash(): string
    {
        $data = [
            'codagt'     => $this->codagt,
            'IDDOC'      => $this->IDDOC,
            'doc_ref'    => $this->doc_ref,
            'dtedeb'     => $this->dtedeb?->format('Y-m-d'),
            'dtefin'     => $this->dtefin?->format('Y-m-d'),
            'flag_actif' => $this->flag_actif,
            'libtype'    => $this->libtype,
            'flag_ligne' => $this->flag_ligne,
        ];

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }
}
