<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserInfoWebservice;
use App\Service\UserSynchronizer;
use App\Service\DocumentSynchronizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class TestWebserviceController extends AbstractController
{
    #[Route('/test-ws', name: 'test_ws', methods: ['GET'])]
    public function testWs(
        Request $request,
        UserInfoWebservice $userWs,
        UserSynchronizer $userSynchronizer,
        DocumentSynchronizer $documentSynchronizer
    ): JsonResponse {
        try {
            /** @var User|null $securityUser */
            $securityUser = $this->getUser();

            // ============================
            // 0) DÉTERMINATION DU USERNAME
            // ============================
            $username = null;

            // ➜ DEV override via header HTTP
            if (
                $this->getParameter('kernel.environment') === 'dev'
                && $request->headers->has('X-DEV-USER')
            ) {
                $username = strtolower(trim(
                    (string) $request->headers->get('X-DEV-USER')
                ));
            }

            // ➜ Sinon SSO normal
            if (!$username) {
                if (!$securityUser) {
                    throw new \RuntimeException('Utilisateur non authentifié');
                }

                $username = strtolower((string) $securityUser->getUsername());
            }

            // ============================
            // OPTIONS DE PILOTAGE
            // ============================
            $fullSync      = (bool) $request->query->get('full', false);
            $syncDocuments = (bool) $request->query->get('documents', true);
            $downloadFiles = (bool) $request->query->get('files', false);

            // ============================
            // 1) WS USER
            // ============================
            $userData = $userWs->fetchUserData($username);

            // ============================
            // 2) SYNCHRONISATION USER
            // ============================
            $user = $userSynchronizer->sync($username, $userData);

            // ============================
            // 3) SYNCHRONISATION DOCUMENTS
            // ============================
            $documentResult = null;

            if ($syncDocuments && $user->getCodagt()) {
                $documentResult = $documentSynchronizer->syncForUser(
                    $user->getCodagt(),
                    false // dryRun = false → écriture DB
                );
            }

            // ============================
            // 4) RÉPONSE
            // ============================
            return $this->json([
                'status' => 'OK',
                'options' => [
                    'full_sync'       => $fullSync,
                    'documents_sync'  => $syncDocuments,
                    'files_download'  => $downloadFiles,
                    'dev_user'        => $request->headers->get('X-DEV-USER'),
                ],
                'user' => [
                    'id'          => $user->getId(),
                    'username'    => $user->getUsername(),
                    'codagt'      => $user->getCodagt(),
                    'nom'         => $user->getNomusu(),
                    'prenom'      => $user->getPrenom(),
                    'mail'        => $user->getMail(),
                    'site'        => $user->getSite(),
                    'service'     => $user->getService(),
                    'roles'       => $user->getRoles(),
                    'compteActif' => $user->getCompteActif(),
                ],
                'documents' => $documentResult ? [
                    'total'   => $documentResult->getTotal(),
                    'created' => $documentResult->getCreated(),
                    'updated' => $documentResult->getUpdated(),
                    'ignored' => $documentResult->getIgnored(),
                ] : null,
            ]);
        } catch (Throwable $e) {
            return $this->json([
                'status'  => 'ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
