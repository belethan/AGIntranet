<?php
namespace App\Security;

final class UserAuthorizationChecker implements UserAuthorizationCheckerInterface
{
    public function isAuthorized(string $username): bool
    {
        // TODO: appeler le WS réel
        return $username !== '';
    }
}
