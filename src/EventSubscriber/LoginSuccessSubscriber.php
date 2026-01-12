<?php

namespace App\EventSubscriber;

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
        $username = $securityUser->getUserIdentifier();

        $this->logger->info('[LOGIN] LoginSuccessSubscriber triggered', [
            'username' => $username,
        ]);

        // 1️⃣ Appel WS identité
        $wsData = $this->userInfoWebservice->fetchUserData($username);

        if (!$wsData) {
            $this->logger->error('[LOGIN] WS UserInfo vide, synchro annulée');
            return;
        }

        // 2️⃣ Synchronisation USER (retourne l’entité User)
        $user = $this->userSynchronizer->sync($username, $wsData);

        // 3️⃣ Synchronisation DOCUMENTS
        if ($user->getCodagt()) {
            $this->documentSynchronizer->sync($user->getCodagt());
        } else {
            $this->logger->warning('[LOGIN] codagt manquant, synchro documents ignorée');
        }
    }
}
