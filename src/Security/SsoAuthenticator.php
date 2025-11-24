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
        // 1) Variables possibles envoyées par Apache / Nginx
        $username = $request->server->get('REMOTE_USER')
            ?? $request->server->get('REDIRECT_REMOTE_USER')
            ?? $request->server->get('PHP_AUTH_USER')
            ?? null;

        if (!$username) {
            throw new AuthenticationException('SSO non actif ou identifiant absent');
        }

        // 2) Normalisation
        // Format 1 : AGDUC\lcoquemert
        if (str_contains($username, '\\')) {
            $username = explode('\\', $username)[1];
        }

        // Format 2 : lcoquemert@agduc.com
        if (str_contains($username, '@')) {
            $username = explode('@', $username)[0];
        }

        // Format 3 : Mise en minuscule pour uniformité
        $username = strtolower(trim($username));

        if (!$username) {
            throw new AuthenticationException('Identifiant SSO vide après normalisation');
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
