<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;

final class UserIdentityResolver
{
    public const DEV_SESSION_KEY = 'dev_remote_user';

    public function __construct(
        private readonly string $appEnv
    ) {}

    public function resolve(Request $request): ?string
    {
        if ($this->appEnv === 'dev') {
            $session = $request->getSession();
            if ($session?->has(self::DEV_SESSION_KEY)) {
                return $session->get(self::DEV_SESSION_KEY);
            }
        }

        return $request->server->get('REMOTE_USER');
    }
}
