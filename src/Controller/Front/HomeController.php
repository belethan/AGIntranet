<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        if (null === $this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front/home.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

}
