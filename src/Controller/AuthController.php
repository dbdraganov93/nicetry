<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use GeoProxy\Service\JwtService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController
{
    #[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $payload = json_decode($request->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        $email = (string) ($payload['email'] ?? '');
        $plan = (string) ($payload['plan'] ?? 'free');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ApiResponse::json(['error' => 'invalid_email'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::json(['status' => 'registered', 'email' => $email, 'plan' => $plan], Response::HTTP_CREATED);
    }

    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(Request $request, JwtService $jwt): Response
    {
        $payload = json_decode($request->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        $email = (string) ($payload['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ApiResponse::json(['error' => 'invalid_credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $secret = (string) ($_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? $_ENV['APP_SECRET'] ?? 'dev-secret');

        return ApiResponse::json([
            'token' => $jwt->issue($email, ['ROLE_USER'], $secret),
            'type' => 'Bearer',
        ]);
    }
}
