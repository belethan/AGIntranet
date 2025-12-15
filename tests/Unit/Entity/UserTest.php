<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testConstructorInitializesDefaults(): void
    {
        $user = new User('john.doe');

        $this->assertSame('john.doe', $user->getCompteinfo());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUsernameIsLowercased(): void
    {
        $user = new User();
        $user->setUsername('John.DOE');

        $this->assertSame('john.doe', $user->getUsername());
        $this->assertSame('john.doe', $user->getUserIdentifier());
    }

    public function testNomAndPrenom(): void
    {
        $user = new User();

        $user->setNomusu('DUPONT')
            ->setPrenom('Jean');

        $this->assertSame('DUPONT', $user->getNomusu());
        $this->assertSame('Jean', $user->getPrenom());
    }

    public function testTelephoneFields(): void
    {
        $user = new User();

        $user->setTeleph('0102030405')
            ->setTelport('0607080910')
            ->setTelpro('0144556677');

        $this->assertSame('0102030405', $user->getTeleph());
        $this->assertSame('0607080910', $user->getTelport());
        $this->assertSame('0144556677', $user->getTelpro());
    }

    public function testNotelAcceptsIntAndString(): void
    {
        $user = new User();

        $user->setNotel(123456);
        $this->assertSame('123456', $user->getNotel());

        $user->setNotel('ABCDEF');
        $this->assertSame('ABCDEF', $user->getNotel());

        $user->setNotel(null);
        $this->assertNull($user->getNotel());
    }

    public function testDates(): void
    {
        $date = new DateTime('1990-05-10');
        $user = new User();

        $user->setDtenai($date);

        $this->assertSame($date, $user->getDtenai());
    }

    public function testRolesAlwaysContainRoleUser(): void
    {
        $user = new User();

        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testPasswordCanBeNullForSSO(): void
    {
        $user = new User();

        $this->assertNull($user->getPassword());

        $user->setPassword('secret');
        $this->assertSame('secret', $user->getPassword());
    }

    public function testCompteActif(): void
    {
        $user = new User();

        $user->setCompteActif(1);

        $this->assertSame(1, $user->getCompteActif());
    }

    public function testExternalHash(): void
    {
        $user = new User();

        $user->setExternalHash('abc123');

        $this->assertSame('abc123', $user->getExternalHash());
    }

    public function testGetInitialesWithNomAndPrenom(): void
    {
        $user = new User();

        $user->setNomusu('Dupont')
            ->setPrenom('Jean');

        $this->assertSame('DJ', $user->getInitiales());
    }

    public function testGetInitialesWithMissingPrenom(): void
    {
        $user = new User();

        $user->setNomusu('Dupont');

        $this->assertSame('D', $user->getInitiales());
    }

    public function testGetInitialesWithNoData(): void
    {
        $user = new User();

        $this->assertSame('', $user->getInitiales());
    }

    public function testEraseCredentialsDoesNothing(): void
    {
        $user = new User();

        // Just ensure it does not throw
        $user->eraseCredentials();

        $this->assertTrue(true);
    }
}
