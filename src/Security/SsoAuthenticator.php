<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SsoAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return true; // SSO actif sur tout le site
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        // Priorité : REMOTE_USER (utilisé par Apache / Nginx)
        $username =
            $_SERVER['REMOTE_USER']
            ?? $_SERVER['USER']
            ?? $_SERVER['LOGNAME']
            ?? trim(shell_exec('whoami'))
            ?? null;

        if (!$username) {
            throw new AuthenticationException('Impossible de déterminer l’utilisateur SSO');
        }

        return new SelfValidatingPassport(new UserBadge($username));
    }

    public function onAuthenticationSuccess(Request $request, $passport, string $firewallName): ?Response
    {
        return null; // Laisse passer la requête
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response(
            "SSO refusé : " . $exception->getMessage(),
            Response::HTTP_FORBIDDEN
        );
    }
}
