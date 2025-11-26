<?php

namespace App\Security;

use App\Service\UserInfoWebservice;
use App\Service\UserSynchronizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SsoAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserInfoWebservice    $userInfoWebservice,
        private readonly UserSynchronizer      $userSynchronizer,
    ) {}

    public function supports(Request $request): ?bool
    {
        // On protège toutes les URL sauf /login (géré par access_control)
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        // 1) Récupération du username SSO
        $username = $this->extractSsoUsername($request);

        if (!$username) {
            throw new AuthenticationException('Impossible de déterminer l’utilisateur SSO.');
        }

        // 2) Passport auto-validant (pas de mot de passe)
        return new SelfValidatingPassport(
            new UserBadge($username)
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $username = $token->getUserIdentifier();

        // 1) Appel du WebService pour récupérer les infos utilisateur
        $wsData = $this->userInfoWebservice->fetchUserData($username);

        // Optionnel : si le WS te dit que l'utilisateur n'a pas le droit :
        // if (($wsData['authorized'] ?? true) === false) {
        //     throw new AuthenticationException('Utilisateur non autorisé sur l’Intranet.');
        // }

        // 2) Synchronisation BDD (création / mise à jour / hash)
        $user = $this->userSynchronizer->sync($username, $wsData);

        // 3) Mise à jour du token avec le User fraîchement synchro
        $token->setUser($user);

        // 4) Redirection vers la page principale (ex: tableau de bord intranet)
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // En cas d’échec SSO ou WS, on renvoie vers la page /login
        // Tu peux y afficher le message d’erreur si besoin.
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    /**
     * Extraction du username à partir des variables serveur SSO (Apache / Nginx).
     */
    private function extractSsoUsername(Request $request): ?string
    {
        $username =
            $request->server->get('REMOTE_USER')
            ?? $request->server->get('REDIRECT_REMOTE_USER')
            ?? null;

        if (!$username) {
            return null;
        }

        // Forme AGDUC\lcoquemert
        if (str_contains($username, '\\')) {
            $parts = explode('\\', $username);
            $username = end($parts);
        }

        // Forme lcoquemert@agduc.com
        if (str_contains($username, '@')) {
            $parts = explode('@', $username);
            $username = $parts[0];
        }

        return strtolower(trim($username));
    }
}
