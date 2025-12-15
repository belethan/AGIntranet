<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class SsoEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, \Throwable $authException = null): Response
    {
        // IMPORTANT :
        // On ne redirige PAS vers /login.
        // On laisse Symfony déclencher l'authenticator SSO.
        throw new AccessDeniedException('Authentification SSO requise');
    }
}
