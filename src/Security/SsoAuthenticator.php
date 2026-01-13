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
        private readonly UserIdentityResolver  $identityResolver,

        #[Autowire(service: 'monolog.logger.sso')]
        private readonly LoggerInterface $ssoLogger,

        #[Autowire(service: 'monolog.logger.agduc')]
        private readonly LoggerInterface $agducLogger,
    ) {}

    public function supports(Request $request): bool
    {
        // Ces routes doivent rester accessibles sans SSO (sinon boucle)
        return !in_array($request->getPathInfo(), [
            '/login',
            '/logout',
            '/test-env',
            '/dev/login',
        ], true);
    }

    public function authenticate(Request $request): Passport
    {
        $username = $this->extractUsername($request);

        if (!$username) {
            $this->ssoLogger->warning('SSO – utilisateur non détecté');
            throw new AuthenticationException('Authentification requise');
        }

        $this->ssoLogger->info('SSO – utilisateur détecté', [
            'username' => $username,
            'source'   => $this->detectIdentitySource($request),
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

                    // Si WS ne renvoie rien => non autorisé / inconnu => on renvoie au login
                    if (empty($wsData)) {
                        $this->agducLogger->warning('AGDUC – WS USER vide / utilisateur non autorisé', [
                            'username' => $identifier,
                        ]);
                        throw new AuthenticationException('Utilisateur non autorisé');
                    }

                    // ============================
                    // 2) Synchronisation USER
                    // ============================
                    $user = $this->userSynchronizer->sync($identifier, $wsData);

                    // Contrôle "actif" si votre Entity l’expose (sécurisé par method_exists)
                    // Adaptez si vous avez un champ/accès spécifique.
                    if (method_exists($user, 'isActif') && !$user->isActif()) {
                        $this->agducLogger->warning('AGDUC – utilisateur inactif', [
                            'username' => $identifier,
                            'user_id'  => $user->getId(),
                        ]);
                        throw new AuthenticationException('Utilisateur non autorisé');
                    }
                    if (method_exists($user, 'isUserActif') && !$user->isUserActif()) {
                        $this->agducLogger->warning('AGDUC – utilisateur inactif', [
                            'username' => $identifier,
                            'user_id'  => $user->getId(),
                        ]);
                        throw new AuthenticationException('Utilisateur non autorisé');
                    }

                    $this->agducLogger->info('AGDUC – utilisateur synchronisé', [
                        'username' => $identifier,
                        'user_id'  => $user->getId(),
                        'codagt'   => $user->getCodagt(),
                    ]);

                    // ============================
                    // 3) Synchronisation DOCUMENTS (dry-run)
                    // ============================
                    if ($user->getCodagt()) {
                        $docResult = $this->documentSynchronizer->syncForUser(
                            $user->getCodagt(),
                            true // dry-run
                        );

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

        // Laisser Symfony continuer le flux normal (HomeController / target path)
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
     * Extraction de l'identité
     * - Priorité 1 : DEV override session (via /dev/login?as=...)
     * - Priorité 2 : PROD SSO (REMOTE_USER/REDIRECT_REMOTE_USER)
     * - Priorité 3 : DEV compat (header/env) si vous l’utilisiez déjà
     */
    private function extractUsername(Request $request): ?string
    {
        // 1) Résolution standard (DEV session override / PROD remote_user)
        $resolved = $this->identityResolver->resolve($request);
        if (is_string($resolved) && trim($resolved) !== '') {
            return $this->normalizeUsername($resolved);
        }

        // 2) Fallback PROD (au cas où, si votre infra pose REDIRECT_REMOTE_USER uniquement)
        $remote =
            $request->server->get('REMOTE_USER')
            ?? $request->server->get('REDIRECT_REMOTE_USER');

        if (is_string($remote) && trim($remote) !== '') {
            return $this->normalizeUsername($remote);
        }

        // 3) DEV fallback historique (header/env)
        $env  = $_ENV['APP_ENV'] ?? 'prod';
        $mode = $_ENV['SSO_MODE'] ?? 'prod';

        if ($env === 'dev' || $mode === 'dev') {
            $headerUser = $request->headers->get('X-DEV-USER');
            if (is_string($headerUser) && trim($headerUser) !== '') {
                $this->ssoLogger->info('SSO DEV – utilisateur via header', [
                    'username' => $headerUser,
                ]);

                return $this->normalizeUsername($headerUser);
            }

            $envUser = $_ENV['SSO_DEV_USER'] ?? null;
            if (is_string($envUser) && trim($envUser) !== '') {
                return $this->normalizeUsername($envUser);
            }
        }

        return null;
    }

    private function detectIdentitySource(Request $request): string
    {
        // Indication log utile pour diagnostiquer les environnements
        $session = $request->getSession();
        if (($session !== null) && $session->has(UserIdentityResolver::DEV_SESSION_KEY)) {
            return 'dev_session_override';
        }
        if ($request->server->has('REMOTE_USER') || $request->server->has('REDIRECT_REMOTE_USER')) {
            return 'remote_user';
        }
        if ($request->headers->has('X-DEV-USER')) {
            return 'dev_header';
        }
        if (!empty($_ENV['SSO_DEV_USER'] ?? null)) {
            return 'dev_env';
        }
        return 'unknown';
    }

    private function normalizeUsername(string $username): string
    {
        $username = trim($username);

        if (str_contains($username, '\\')) {
            $username = substr($username, strrpos($username, '\\') + 1);
        }

        if (str_contains($username, '@')) {
            $username = substr($username, 0, strpos($username, '@'));
        }

        return strtolower(trim($username));
    }
}
