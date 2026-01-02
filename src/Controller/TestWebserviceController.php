<?php

namespace App\Controller;

use App\Service\UserInfoWebservice;
use App\Service\UserSynchronizer;
use App\Service\DocumentSynchronizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class TestWebserviceController extends AbstractController
{
    #[Route('/test-ws', name: 'test_ws', methods: ['GET'])]
    public function testWs(
        UserInfoWebservice $userWs,
        UserSynchronizer $userSynchronizer,
        DocumentSynchronizer $documentSynchronizer
    ): JsonResponse {
        // Utilisateur SSO simulé en environnement DEV
        $username = $_ENV['SSO_DEV_USER'] ?? 'lfournier';

        try {
            /**
             * 1. Récupération des données utilisateur depuis le WS
             */
            $userData = $userWs->fetchUserData($username);

            /**
             * 2. Synchronisation User (création ou mise à jour)
             */
            $user = $userSynchronizer->sync($username, $userData);

            /**
             * 3. Synchronisation des documents associés à l'utilisateur
             */
            $documentSyncResult = $documentSynchronizer->syncForUser($user);

            /**
             * 4. Réponse JSON structurée
             */
            return $this->json([
                'status' => 'OK',
                'user' => [
                    'id'       => $user->getId(),
                    'username' => $username,
                    'nom'      => $user->getNomusu(),
                    'prenom'   => $user->getPrenom(),
                    'mail'     => $user->getMail(),
                    'site'     => $user->getSite(),
                    'service'  => $user->getService(),
                ],
                'documents' => [
                    'total'   => $documentSyncResult->getTotal(),
                    'created' => $documentSyncResult->getCreated(),
                    'updated' => $documentSyncResult->getUpdated(),
                    'ignored' => $documentSyncResult->getIgnored(),
                ],
            ]);
        } catch (Throwable $e) {
            return $this->json([
                'status'  => 'ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
