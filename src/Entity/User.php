<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $nomusu = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $nompat = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $teleph = null;

    #[ORM\Column]
    private ?int $sexe = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dtenai = null;

    #[ORM\Column(length: 100)]
    private ?string $comnai = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $telpro = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailpro = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $telport = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $notel = null;

    #[ORM\Column(length: 120)]
    private ?string $compteinfo = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $site = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $service = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $serviceresp = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $nomcj = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenomcj = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $datenaicj = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $codnat = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $contacc = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $telacc = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $telportacc = null;

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

    #[ORM\Column(length: 7)]
    private ?string $codagt = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $roles = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $telportpro = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $compte_info = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $num_rpps = null;

    #[ORM\Column]
    private ?int $compte_actif = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomusu(): ?string
    {
        return $this->nomusu;
    }

    public function setNomusu(string $nomusu): static
    {
        $this->nomusu = $nomusu;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNompat(): ?string
    {
        return $this->nompat;
    }

    public function setNompat(?string $nompat): static
    {
        $this->nompat = $nompat;

        return $this;
    }

    public function getTeleph(): ?string
    {
        return $this->teleph;
    }

    public function setTeleph(?string $teleph): static
    {
        $this->teleph = $teleph;

        return $this;
    }

    public function getSexe(): ?int
    {
        return $this->sexe;
    }

    public function setSexe(int $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getDtenai(): ?\DateTime
    {
        return $this->dtenai;
    }

    public function setDtenai(?\DateTime $dtenai): static
    {
        $this->dtenai = $dtenai;

        return $this;
    }

    public function getComnai(): ?string
    {
        return $this->comnai;
    }

    public function setComnai(string $comnai): static
    {
        $this->comnai = $comnai;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getTelpro(): ?string
    {
        return $this->telpro;
    }

    public function setTelpro(?string $telpro): static
    {
        $this->telpro = $telpro;

        return $this;
    }

    public function getMailpro(): ?string
    {
        return $this->mailpro;
    }

    public function setMailpro(?string $mailpro): static
    {
        $this->mailpro = $mailpro;

        return $this;
    }

    public function getTelport(): ?string
    {
        return $this->telport;
    }

    public function setTelport(?string $telport): static
    {
        $this->telport = $telport;

        return $this;
    }

    public function getNotel(): ?string
    {
        return $this->notel;
    }

    public function setNotel(?string $notel): static
    {
        $this->notel = $notel;

        return $this;
    }

    public function getCompteinfo(): ?string
    {
        return $this->compteinfo;
    }

    public function setCompteinfo(string $compteinfo): static
    {
        $this->compteinfo = $compteinfo;

        return $this;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(?string $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getServiceresp(): ?string
    {
        return $this->serviceresp;
    }

    public function setServiceresp(?string $serviceresp): static
    {
        $this->serviceresp = $serviceresp;

        return $this;
    }

     public function getNomcj(): ?string
    {
        return $this->nomcj;
    }

    public function setNomcj(?string $nomcj): static
    {
        $this->nomcj = $nomcj;

        return $this;
    }

    public function getPrenomcj(): ?string
    {
        return $this->prenomcj;
    }

    public function setPrenomcj(?string $prenomcj): static
    {
        $this->prenomcj = $prenomcj;

        return $this;
    }

    public function getDatenaicj(): ?\DateTimeImmutable
    {
        return $this->datenaicj;
    }

    public function setDatenaicj(?\DateTimeImmutable $datenaicj): static
    {
        $this->datenaicj = $datenaicj;

        return $this;
    }

    public function getCodnat(): ?string
    {
        return $this->codnat;
    }

    public function setCodnat(?string $codnat): static
    {
        $this->codnat = $codnat;

        return $this;
    }

    public function getContacc(): ?string
    {
        return $this->contacc;
    }

    public function setContacc(?string $contacc): static
    {
        $this->contacc = $contacc;

        return $this;
    }

    public function getTelacc(): ?string
    {
        return $this->telacc;
    }

    public function setTelacc(?string $telacc): static
    {
        $this->telacc = $telacc;

        return $this;
    }

    public function getTelportacc(): ?string
    {
        return $this->telportacc;
    }

    public function setTelportacc(?string $telportacc): static
    {
        $this->telportacc = $telportacc;

        return $this;
    }

    public function getLibcom(): ?string
    {
        return $this->libcom;
    }

    public function setLibcom(?string $libcom): static
    {
        $this->libcom = $libcom;

        return $this;
    }

    public function getCodpos(): ?string
    {
        return $this->codpos;
    }

    public function setCodpos(?string $codpos): static
    {
        $this->codpos = $codpos;

        return $this;
    }

    public function getCodpay(): ?string
    {
        return $this->codpay;
    }

    public function setCodpay(?string $codpay): static
    {
        $this->codpay = $codpay;

        return $this;
    }

    public function getNomrue(): ?string
    {
        return $this->nomrue;
    }

    public function setNomrue(?string $nomrue): static
    {
        $this->nomrue = $nomrue;

        return $this;
    }

    public function getNumrue(): ?string
    {
        return $this->numrue;
    }

    public function setNumrue(?string $numrue): static
    {
        $this->numrue = $numrue;

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

    public function getRoles(): ?string
    {
        return $this->roles;
    }

    public function setRoles(?string $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getTelportpro(): ?string
    {
        return $this->telportpro;
    }

    public function setTelportpro(?string $telportpro): static
    {
        $this->telportpro = $telportpro;

        return $this;
    }

    public function getNumRpps(): ?string
    {
        return $this->num_rpps;
    }

    public function setNumRpps(?string $num_rpps): static
    {
        $this->num_rpps = $num_rpps;

        return $this;
    }

    public function getCompteActif(): ?int
    {
        return $this->compte_actif;
    }

    public function setCompteActif(int $compte_actif): static
    {
        $this->compte_actif = $compte_actif;

        return $this;
    }

    public function getCodagtResponsable(): ?string
    {
        return $this->codagtResponsable;
    }

    public function setCodagtResponsable(?string $codagtResponsable): static
    {
        $this->codagtResponsable = $codagtResponsable;

        return $this;
    }

    public function getNomResponsable(): ?string
    {
        return $this->nomResponsable;
    }

    public function setNomResponsable(?string $nomResponsable): static
    {
        $this->nomResponsable = $nomResponsable;

        return $this;
    }

    public function getPrenomResponsable(): ?string
    {
        return $this->prenomResponsable;
    }

    public function setPrenomResponsable(?string $prenomResponsable): static
    {
        $this->prenomResponsable = $prenomResponsable;

        return $this;
    }

    public function getMailResponsable(): ?string
    {
        return $this->mailResponsable;
    }

    public function setMailResponsable(?string $mailResponsable): static
    {
        $this->mailResponsable = $mailResponsable;

        return $this;
    }

    public function getSiteresp(): ?string
    {
        return $this->siteresp;
    }

    public function setSiteresp(?string $siteresp): static
    {
        $this->siteresp = $siteresp;

        return $this;
    }
}
