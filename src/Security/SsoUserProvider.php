<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SsoUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserRepository $repo
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // 1. On cherche l'utilisateur dans MySQL
        $user = $this->repo->findOneBy(['compteinfo' => $identifier]);

        if ($user) {
            return $user;
        }

        // 2. Si non trouvé : on retourne un user minimal
        //    Il sera hydraté + créé en DB dans onAuthenticationSuccess() via UserSynchronizer
        return new User($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new \RuntimeException('Invalid user class !');
        }

        // IMPORTANT : on recharge depuis Doctrine pour rester synchro
        // Si l'utilisateur n’est pas encore en DB, on retourne l'objet minimal
        return $this->repo->findOneBy(['compteinfo' => $user->getCompteinfo()]) ?? $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}
