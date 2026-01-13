<?php
namespace App\Security;

interface UserAuthorizationCheckerInterface
{
    public function isAuthorized(string $username): bool;
}
