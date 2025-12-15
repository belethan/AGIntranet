<?php

namespace App\Tests\Factory;

use App\Entity\User;

final class UserFactory
{
    public static function createValid(): User
    {
        $user = new User();

        // Identifiant Symfony
        $user->setUsername('test.user');

        // Champs NOT NULL en base
        $user->setNomusu('TEST');
        $user->setPrenom('User');
        $user->setSexe(1);
        $user->setCompteActif(1);
        $user->setComnai('FR');
        $user->setCodagt('9000');
        // Champs facultatifs
        $user->setMail('test.user@test.fr');

        return $user;
    }
}
