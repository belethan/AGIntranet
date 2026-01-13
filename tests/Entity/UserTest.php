<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testConstructorInitializesRoleUser(): void
    {
        $user = new User();
        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testUserIdentifierReturnsLowercasedUsername(): void
    {
        $user = new User();
        $user->setUsername('LCOQUEMERT');

        $this->assertSame('lcoquemert', $user->getUserIdentifier());
        $this->assertSame('lcoquemert', $user->getUsername());
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
        $user = new User('jdupont');

        $user
            ->setNomusu('DUPONT')
            ->setPrenom('Jean')
            ->setCodagt('AG123')
            ->setCompteActif(1);

        $this->assertSame('DUPONT', $user->getNomusu());
        $this->assertSame('Jean', $user->getPrenom());
        $this->assertSame('AG123', $user->getCodagt());
        $this->assertSame(1, $user->getCompteActif());
    }

    public function testInitialesComputation(): void
    {
        $user = new User();
        $user->setNomusu('dupont')->setPrenom('jean');

        $this->assertSame('DJ', $user->getInitiales());
    }

    public function testSecurityFields(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');
        $user->setExternalHash('EXT_HASH');

        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertSame('EXT_HASH', $user->getExternalHash());
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        $user = new User();
        $user->eraseCredentials();
        $this->assertTrue(true);
    }
}
