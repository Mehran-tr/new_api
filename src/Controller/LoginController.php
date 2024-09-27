<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LoginController
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(UserInterface $user): JsonResponse
    {
        if (!$user instanceof UserInterface) {
            throw new AuthenticationException('Invalid credentials.');
        }

        // Generate JWT token for the authenticated user
        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
