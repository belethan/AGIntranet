<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SsoUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new class($identifier) implements UserInterface {
            public function __construct(private string $username) {}

            public function getUserIdentifier(): string { return $this->username; }
            public function getRoles(): array { return ['ROLE_USER']; }
            public function eraseCredentials(): void {}
        };
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user; // stateless
    }

    public function supportsClass(string $class): bool
    {
        return true;
    }
}

