<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testConstructorInitializesRoleUser(): void
    {
        $user = new User();

        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testUserIdentifierReturnsUsername(): void
    {
        $user = new User();
        $user->setUsername('LCOQUEMERT');

        $this->assertSame('lcoquemert', $user->getUserIdentifier());
    }

    public function testUsernameIsLowercased(): void
    {
        $user = new User();
        $user->setUsername('TESTUSER');

        $this->assertSame('testuser', $user->getUsername());
    }

    public function testRolesAlwaysContainRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testBasicIdentityFields(): void
    {
        $user = new User();

        $user
            ->setNomusu('DUPONT')
            ->setPrenom('Jean')
            ->setNompat('DURAND')
            ->setSexe(1)
            ->setComnai('PARIS')
            ->setCodagt('AG123')
            ->setCompteActif(1);

        $this->assertSame('DUPONT', $user->getNomusu());
        $this->assertSame('Jean', $user->getPrenom());
        $this->assertSame('DURAND', $user->getNompat());
        $this->assertSame(1, $user->getSexe());
        $this->assertSame('PARIS', $user->getComnai());
        $this->assertSame('AG123', $user->getCodagt());
        $this->assertSame(1, $user->getCompteActif());
    }

    public function testContactFields(): void
    {
        $user = new User();

        $user
            ->setMail('test@agduc.fr')
            ->setMailpro('pro@agduc.fr')
            ->setTeleph('0102030405')
            ->setTelport('0607080910')
            ->setTelpro('0155555555')
            ->setTelportpro('0677777777')
            ->setNotel(123456);

        $this->assertSame('test@agduc.fr', $user->getMail());
        $this->assertSame('pro@agduc.fr', $user->getMailpro());
        $this->assertSame('0102030405', $user->getTeleph());
        $this->assertSame('0607080910', $user->getTelport());
        $this->assertSame('0155555555', $user->getTelpro());
        $this->assertSame('0677777777', $user->getTelportpro());
        $this->assertSame('123456', $user->getNotel());
    }

    public function testDatesAreHandledCorrectly(): void
    {
        $user = new User();

        $birth = new DateTime('1990-01-01');
        $birthCj = new DateTimeImmutable('1992-02-02');

        $user
            ->setDtenai($birth)
            ->setDatenaicj($birthCj);

        $this->assertSame($birth, $user->getDtenai());
        $this->assertSame($birthCj, $user->getDatenaicj());
    }

    public function testAddressFields(): void
    {
        $user = new User();

        $user
            ->setNomrue('Rue de la Paix')
            ->setNumrue('10')
            ->setCodpos('75001')
            ->setCodpay('FR')
            ->setLibcom('PARIS');

        $this->assertSame('Rue de la Paix', $user->getNomrue());
        $this->assertSame('10', $user->getNumrue());
        $this->assertSame('75001', $user->getCodpos());
        $this->assertSame('FR', $user->getCodpay());
        $this->assertSame('PARIS', $user->getLibcom());
    }

    public function testResponsableFields(): void
    {
        $user = new User();

        $user
            ->setCodagtResponsable('AG999')
            ->setNomResponsable('RESP')
            ->setPrenomResponsable('Paul')
            ->setMailResponsable('resp@agduc.fr')
            ->setSiteresp('SITE-A');

        $this->assertSame('AG999', $user->getCodagtResponsable());
        $this->assertSame('RESP', $user->getNomResponsable());
        $this->assertSame('Paul', $user->getPrenomResponsable());
        $this->assertSame('resp@agduc.fr', $user->getMailResponsable());
        $this->assertSame('SITE-A', $user->getSiteresp());
    }

    public function testSecurityFields(): void
    {
        $user = new User();

        $user
            ->setPassword('hashed_password')
            ->setExternalHash('EXT_HASH');

        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertSame('EXT_HASH', $user->getExternalHash());
    }

    public function testInitialesComputation(): void
    {
        $user = new User();

        $user
            ->setNomusu('dupont')
            ->setPrenom('jean');

        $this->assertSame('DJ', $user->getInitiales());
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        $user = new User();

        $user->eraseCredentials();

        $this->assertTrue(true);
    }
}
