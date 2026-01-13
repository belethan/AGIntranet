<?php

namespace App\Tests\Factory;

use App\Entity\User;

final class UserFactory
{
    /**
     * Crée un utilisateur valide pour les tests Repository
     */
    public static function createValid(
        string $username = 'test.user',
        string $codagt = '9000'
    ): User {
        // Le constructeur initialise username, compteInfo, roles, compte_actif
        $user = new User($username);

        // Champs STRICTEMENT obligatoires et disponibles
        $user
            ->setCodagt($codagt)
            ->setNomusu('TEST')
            ->setPrenom('User');

        // Hash externe (utilisé par la logique métier)
        $user->setExternalHash(
            hash('sha256', $username . '|' . $codagt)
        );

        return $user;
    }
}
