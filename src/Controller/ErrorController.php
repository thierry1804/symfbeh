<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ErrorController extends AbstractController
{
    #[Route('/error/403', name: 'app_error_403')]
    public function accessDenied(): Response
    {
        return $this->render('error/403.html.twig', [], new Response('', 403));
    }
}
