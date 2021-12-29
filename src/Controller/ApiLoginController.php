<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiLoginController extends AbstractController
{
  #[Route('/login', name: 'api_login')]
  public function index(): Response
  {
    if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
      return $this->json([
            'email' => $this->getUser()->getEmail(),
        'api_token' => $this->getUser()->getApiToken()
      ]);
    } else {
      return $this->json([
        'error' => 'Invalid credentials.'
      ], Response::HTTP_UNAUTHORIZED);
    }
  }
}