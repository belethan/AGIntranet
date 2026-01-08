<?php

namespace App\Security;

use App\Entity\User;
use App\Service\DocumentSynchronizer;
use App\Service\UserInfoWebservice;
use App\Service\UserSynchronizer;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class SsoAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserInfoWebservice    $userInfoWebservice,
        private readonly UserSynchronizer      $userSynchronizer,
        private readonly DocumentSynchronizer  $documentSynchronizer,

        #[Autowire(service: 'monolog.logger.sso')]
        private readonly LoggerInterface $ssoLogger,

        #[Autowire(service: 'monolog.logger.agduc')]
        private readonly LoggerInterface $agducLogger,
    ) {}

    public function supports(Request $request): bool
    {
        return !in_array($request->getPathInfo(), [
            '/login',
            '/logout',
            '/test-env',
        ], true);
    }

    public function authenticate(Request $request): Passport
    {
        $username = $this->extractSsoUsername($request);

        if (!$username) {
            $this->ssoLogger->warning('SSO – utilisateur non détecté');
            throw new AuthenticationException('Authentification SSO requise');
        }

        $this->ssoLogger->info('SSO – utilisateur détecté', [
            'username' => $username,
        ]);

        return new SelfValidatingPassport(
            new UserBadge(
                $username,
                function (string $identifier): User {

                    // ============================
                    // 1) WS Oracle USER
                    // ============================
                    $this->agducLogger->info('AGDUC – appel WS USER', [
                        'username' => $identifier,
                    ]);

                    $wsData = $this->userInfoWebservice->fetchUserData($identifier);

                    // ============================
                    // 2) Synchronisation USER
                    // ============================
                    $user = $this->userSynchronizer->sync($identifier, $wsData);

                    $this->agducLogger->info('AGDUC – utilisateur synchronisé', [
                        'username' => $identifier,
                        'user_id'  => $user->getId(),
                        'codagt'   => $user->getCodagt(),
                    ]);

                    // ============================
                    // 3) Synchronisation DOCUMENTS (dry-run)
                    // ============================
                    if ($user->getCodagt()) {
                        $docResult = $this->documentSynchronizer
                            ->syncForUser($user->getCodagt(), true);

                        $this->agducLogger->info('AGDUC – documents (dry-run)', [
                            'username' => $identifier,
                            'codagt'   => $user->getCodagt(),
                            'total'    => $docResult->getTotal(),
                            'created'  => $docResult->getCreated(),
                            'updated'  => $docResult->getUpdated(),
                            'ignored'  => $docResult->getIgnored(),
                        ]);
                    }

                    return $user;
                }
            )
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        $this->ssoLogger->info('SSO – authentification réussie', [
            'username' => $token->getUserIdentifier(),
        ]);

        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): ?Response {
        $this->ssoLogger->error('SSO – authentification échouée', [
            'message' => $exception->getMessage(),
        ]);

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    /**
     * 🔐 Extraction USER SSO
     * - PROD : REMOTE_USER
     * - DEV  : Header X-DEV-USER
     */
    private function extractSsoUsername(Request $request): ?string
    {
        $env  = $_ENV['APP_ENV'] ?? 'prod';
        $mode = $_ENV['SSO_MODE'] ?? 'prod';

        // ============================
        // 1) PROD — vrai SSO
        // ============================
        $username =
            $request->server->get('REMOTE_USER')
            ?? $request->server->get('REDIRECT_REMOTE_USER');

        if ($username) {
            return $this->normalizeUsername($username);
        }

        // ============================
        // 2) DEV — header contrôlé
        // ============================
        if ($env === 'dev' || $mode === 'dev') {
            $headerUser = $request->headers->get('X-DEV-USER');

            if ($headerUser) {
                $this->ssoLogger->info('SSO DEV – utilisateur via header', [
                    'username' => $headerUser,
                ]);

                return strtolower(trim($headerUser));
            }

            // fallback env
            $envUser = $_ENV['SSO_DEV_USER'] ?? null;
            if ($envUser) {
                return strtolower(trim($envUser));
            }
        }

        return null;
    }

    private function normalizeUsername(string $username): string
    {
        if (str_contains($username, '\\')) {
            $username = substr($username, strrpos($username, '\\') + 1);
        }

        if (str_contains($username, '@')) {
            $username = substr($username, 0, strpos($username, '@'));
        }

        return strtolower(trim($username));
    }
}
