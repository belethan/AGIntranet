<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: 'uniq_user_codagt', columns: ['codagt'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // ===============================
    // CLÉ TECHNIQUE
    // ===============================

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ===============================
    // AUTH / SSO
    // ===============================

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(name: 'compte_info', length: 50, nullable: true)]
    private ?string $compteInfo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalHash = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private int $compte_actif = 1;

    // ===============================
    // IDENTITÉ AGDUC
    // ===============================

    #[ORM\Column(length: 7, unique: true)]
    private ?string $codagt = null;

    #[ORM\Column(length: 120)]
    private ?string $nomusu = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $nompat = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $teleph = null;

    #[ORM\Column(nullable: true)]
    private ?int $sexe = 1;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dtenai = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $comnai = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailpro = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $telpro = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $telport = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $telportpro = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $notel = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $site = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $service = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $serviceresp = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numRpps = null;

    // ===============================
    // CONJOINT / CONTACT
    // ===============================

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $nomcj = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenomcj = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $datenaicj = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $codnat = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $contacc = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $telacc = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $telportacc = null;

    // ===============================
    // ADRESSE
    // ===============================

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $libcom = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $codpos = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $codpay = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomrue = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $numrue = null;

    // ===============================
    // RESPONSABLE
    // ===============================

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $codagtResponsable = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $nomResponsable = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenomResponsable = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $mailResponsable = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $siteresp = null;

    // ===============================
    // CONSTRUCTEUR
    // ===============================

    public function __construct(string $username = '')
    {
        if ($username !== '') {
            $this->username = strtolower($username);
            $this->compteInfo = strtolower($username);
        }

        $this->roles = ['ROLE_USER'];
        $this->compte_actif = 1;
    }

    // ===============================
    // SECURITY
    // ===============================

    public function getUserIdentifier(): string
    {
        return $this->username ?? throw new \LogicException('User has no username');
    }

    public function eraseCredentials(): void {}

    public function getRoles(): array
    {
        return array_unique([...$this->roles, 'ROLE_USER']);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(?string $password): self { $this->password = $password; return $this; }

    // ===============================
    // GETTERS / SETTERS
    // ===============================

    public function getId(): ?int { return $this->id; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(string $v): self { $this->username = strtolower($v); return $this; }

    public function getCompteInfo(): ?string { return $this->compteInfo; }
    public function setCompteInfo(?string $v): self { $this->compteInfo = $v ? strtolower($v) : null; return $this; }

    public function getCodagt(): ?string { return $this->codagt; }
    public function setCodagt(string $v): self { $this->codagt = $v; return $this; }

    public function getExternalHash(): ?string { return $this->externalHash; }
    public function setExternalHash(?string $v): self { $this->externalHash = $v; return $this; }

    public function getCompteActif(): int { return $this->compte_actif; }
    public function setCompteActif(int $v): self { $this->compte_actif = $v; return $this; }

    public function getNomusu(): ?string { return $this->nomusu; }
    public function setNomusu(string $v): self { $this->nomusu = $v; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $v): self { $this->prenom = $v; return $this; }

    public function getNompat(): ?string { return $this->nompat; }
    public function setNompat(?string $v): self { $this->nompat = $v; return $this; }

    public function getSexe(): ?int { return $this->sexe; }
    public function setSexe(?int $v): self { $this->sexe = $v; return $this; }

    public function getTeleph(): ?string { return $this->teleph; }
    public function setTeleph(?string $v): self { $this->teleph = $v; return $this; }

    public function getDtenai(): ?DateTimeImmutable { return $this->dtenai; }
    public function setDtenai(?DateTimeImmutable $v): self { $this->dtenai = $v; return $this; }

    public function getComnai(): ?string { return $this->comnai; }
    public function setComnai(?string $v): self { $this->comnai = $v; return $this; }

    public function getMail(): ?string { return $this->mail; }
    public function setMail(?string $v): self { $this->mail = $v; return $this; }

    public function getMailpro(): ?string { return $this->mailpro; }
    public function setMailpro(?string $v): self { $this->mailpro = $v; return $this; }

    public function getTelpro(): ?string { return $this->telpro; }
    public function setTelpro(?string $v): self { $this->telpro = $v; return $this; }

    public function getTelport(): ?string { return $this->telport; }
    public function setTelport(?string $v): self { $this->telport = $v; return $this; }

    public function getTelportpro(): ?string { return $this->telportpro; }
    public function setTelportpro(?string $v): self { $this->telportpro = $v; return $this; }

    public function getNotel(): ?string { return $this->notel; }
    public function setNotel(?string $v): self { $this->notel = $v; return $this; }

    public function getSite(): ?string { return $this->site; }
    public function setSite(?string $v): self { $this->site = $v; return $this; }

    public function getService(): ?string { return $this->service; }
    public function setService(?string $v): self { $this->service = $v; return $this; }

    public function getServiceresp(): ?string { return $this->serviceresp; }
    public function setServiceresp(?string $v): self { $this->serviceresp = $v; return $this; }

    public function getNumRpps(): ?string { return $this->numRpps; }
    public function setNumRpps(?string $v): self { $this->numRpps = $v; return $this; }

    public function getNomcj(): ?string { return $this->nomcj; }
    public function setNomcj(?string $v): self { $this->nomcj = $v; return $this; }

    public function getPrenomcj(): ?string { return $this->prenomcj; }
    public function setPrenomcj(?string $v): self { $this->prenomcj = $v; return $this; }

    public function getDatenaicj(): ?DateTimeImmutable { return $this->datenaicj; }
    public function setDatenaicj(?DateTimeImmutable $v): self { $this->datenaicj = $v; return $this; }

    public function getCodnat(): ?string { return $this->codnat; }
    public function setCodnat(?string $v): self { $this->codnat = $v; return $this; }

    public function getContacc(): ?string { return $this->contacc; }
    public function setContacc(?string $v): self { $this->contacc = $v; return $this; }

    public function getTelacc(): ?string { return $this->telacc; }
    public function setTelacc(?string $v): self { $this->telacc = $v; return $this; }

    public function getTelportacc(): ?string { return $this->telportacc; }
    public function setTelportacc(?string $v): self { $this->telportacc = $v; return $this; }

    public function getLibcom(): ?string { return $this->libcom; }
    public function setLibcom(?string $v): self { $this->libcom = $v; return $this; }

    public function getCodpos(): ?string { return $this->codpos; }
    public function setCodpos(?string $v): self { $this->codpos = $v; return $this; }

    public function getCodpay(): ?string { return $this->codpay; }
    public function setCodpay(?string $v): self { $this->codpay = $v; return $this; }

    public function getNomrue(): ?string { return $this->nomrue; }
    public function setNomrue(?string $v): self { $this->nomrue = $v; return $this; }

    public function getNumrue(): ?string { return $this->numrue; }
    public function setNumrue(?string $v): self { $this->numrue = $v; return $this; }

    public function getCodagtResponsable(): ?string { return $this->codagtResponsable; }
    public function setCodagtResponsable(?string $v): self { $this->codagtResponsable = $v; return $this; }

    public function getNomResponsable(): ?string { return $this->nomResponsable; }
    public function setNomResponsable(?string $v): self { $this->nomResponsable = $v; return $this; }

    public function getPrenomResponsable(): ?string { return $this->prenomResponsable; }
    public function setPrenomResponsable(?string $v): self { $this->prenomResponsable = $v; return $this; }

    public function getMailResponsable(): ?string { return $this->mailResponsable; }
    public function setMailResponsable(?string $v): self { $this->mailResponsable = $v; return $this; }

    public function getSiteresp(): ?string { return $this->siteresp; }
    public function setSiteresp(?string $v): self { $this->siteresp = $v; return $this; }

    public function getInitiales(): string
    {
        return mb_strtoupper(
            ($this->nomusu ? mb_substr($this->nomusu, 0, 1) : '') .
            ($this->prenom ? mb_substr($this->prenom, 0, 1) : '')
        );
    }
}
