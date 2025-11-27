<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class SsoEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, \Throwable $authException = null): \Symfony\Component\HttpFoundation\Response
    {
        // Si pas authentifié → on force simplement la page racine
        return new RedirectResponse('/');
    }
}

