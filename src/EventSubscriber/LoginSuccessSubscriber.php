<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\UserSynchronizer;
use App\Service\DocumentSynchronizer;
use App\Service\UserInfoWebservice;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserInfoWebservice   $userInfoWebservice,
        private readonly UserSynchronizer     $userSynchronizer,
        private readonly DocumentSynchronizer $documentSynchronizer,
        private readonly LoggerInterface      $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $securityUser = $event->getUser();

        // Sécurité : on ne traite que notre entité User
        if (!$securityUser instanceof User) {
            $this->logger->warning('[LOGIN] Utilisateur non supporté', [
                'class' => get_debug_type($securityUser),
            ]);
            return;
        }

        $username = $securityUser->getUserIdentifier();

        $this->logger->info('[LOGIN] LoginSuccessSubscriber triggered', [
            'username' => $username,
        ]);

        // =====================================================
        // 1) APPEL WS IDENTITÉ
        // =====================================================
        try {
            $wsData = $this->userInfoWebservice->fetchUserData($username);
        } catch (\Throwable $e) {
            $this->logger->error('[LOGIN] Erreur WS UserInfo', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);
            return;
        }

        if (empty($wsData) || empty($wsData['codagt'])) {
            $this->logger->error('[LOGIN] WS UserInfo invalide ou codagt manquant', [
                'username' => $username,
                'ws_keys'  => is_array($wsData) ? array_keys($wsData) : null,
            ]);
            return;
        }

        // =====================================================
        // 2) SYNCHRONISATION USER
        // =====================================================
        try {
            $user = $this->userSynchronizer->sync($username, $wsData);
        } catch (\Throwable $e) {
            $this->logger->error('[LOGIN] Erreur synchro USER', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);
            return;
        }

        // =====================================================
        // 3) SYNCHRONISATION DOCUMENTS (ÉCRITURE RÉELLE)
        // =====================================================
        if ($user->getCodagt()) {
            try {
                $result = $this->documentSynchronizer->syncForUser(
                    $user->getCodagt(),
                    false // ❗ false = écriture DB réelle
                );

                $this->logger->info('[LOGIN] Documents synchronisés', [
                    'codagt'  => $user->getCodagt(),
                    'total'   => $result->getTotal(),
                    'created' => $result->getCreated(),
                    'updated' => $result->getUpdated(),
                    'ignored' => $result->getIgnored(),
                ]);
            } catch (\Throwable $e) {
                $this->logger->error('[LOGIN] Erreur synchro DOCUMENTS', [
                    'codagt' => $user->getCodagt(),
                    'error'  => $e->getMessage(),
                ]);
            }
        } else {
            $this->logger->warning('[LOGIN] codagt manquant après synchro USER', [
                'username' => $username,
            ]);
        }
    }
}
