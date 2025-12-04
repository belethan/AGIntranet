<?php

namespace App\Controller;

use App\Service\UserInfoWebservice;
use App\Service\UserSynchronizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestWebserviceController extends AbstractController
{
    #[Route('/test-ws', name: 'test_ws')]
    public function testWs(
        UserInfoWebservice $ws,
        UserSynchronizer $synchronizer
    ): JsonResponse
    {
        $username = $_ENV['SSO_DEV_USER'] ?? 'lfournier';
        dump('Start of the process', $username); // <-- Ajoute ça ici

        try {
            $data = $ws->fetchUserData($username);
            $user = $synchronizer->sync($username, $data);  // <-- Appel de la méthode sync()
            return $this->json([
                'status' => 'OK',
                'user_id' => $user->getId(),
                'nom' => $user->getNomusu(),
                'prenom' => $user->getPrenom(),
                'mail' => $user->getMail(),
                'site' => $user->getSite(),
                'service' => $user->getService(),
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }



}

