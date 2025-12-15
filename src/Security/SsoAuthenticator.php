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

final class SsoAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserInfoWebservice    $userInfoWebservice,
        private readonly UserSynchronizer      $userSynchronizer,
        private readonly SsoUserProvider       $userProvider, // ✅ SERVICE CONCRET
    ) {}

    public function supports(Request $request): ?bool
    {
        if (in_array($request->getPathInfo(), [
            '/login',
            '/test-ws',
            '/test-env',
        ], true)) {
            return false;
        }

        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $username = $this->extractSsoUsername($request);

        file_put_contents(
            dirname(__DIR__, 2).'/var/log/sso.log',
            sprintf("[%s] USERNAME SSO = %s\n", date('Y-m-d H:i:s'), var_export($username, true)),
            FILE_APPEND
        );

        if (!$username) {
            throw new AuthenticationException('Impossible de déterminer l’utilisateur SSO.');
        }

        if (!$username) {
            throw new AuthenticationException('Impossible de déterminer l’utilisateur SSO.');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $username,
                fn (string $identifier) => $this->userProvider->loadUserByIdentifier($identifier)
            )
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        $username = $token->getUserIdentifier();

        $wsData = $this->userInfoWebservice->fetchUserData($username);
        $user   = $this->userSynchronizer->sync($username, $wsData);

        $token->setUser($user);

        // Laisser Symfony continuer la requête normalement
        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): ?Response {
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    private function extractSsoUsername(Request $request): ?string
    {
        $mode = $request->server->get('SSO_MODE')
            ?? getenv('SSO_MODE')
            ?? $_ENV['SSO_MODE']
            ?? 'prod';

        if ($mode === 'dev') {
            return strtolower(
                $request->server->get('SSO_DEV_USER')
                ?? getenv('SSO_DEV_USER')
                ?? 'lcoquemert'
            );
        }

        $username =
            $request->server->get('REMOTE_USER')
            ?? $request->server->get('REDIRECT_REMOTE_USER');

        if (!$username) {
            return null;
        }

        if (str_contains($username, '\\')) {
            $username = substr($username, strrpos($username, '\\') + 1);
        }

        if (str_contains($username, '@')) {
            $username = substr($username, 0, strpos($username, '@'));
        }

        return strtolower(trim($username));
    }
}
