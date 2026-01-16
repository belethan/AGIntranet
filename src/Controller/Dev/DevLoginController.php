<?php

namespace App\Controller\Dev;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DevLoginController extends AbstractController
{
    #[Route('/dev/login', name: 'dev_login')]
    public function __invoke(): Response
    {
        return new Response('Dev login OK');
    }
}
