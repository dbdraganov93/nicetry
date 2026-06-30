<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Repository\FixtureRepository;
use GeoProxy\Service\ApiResponse;
use GeoProxy\Service\ClientOnboardingService;
use GeoProxy\Service\JwtService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController
{
    #[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request, ?ClientOnboardingService $onboarding = null): Response
    {
        $payload = $this->payload($request);
        $email = (string) ($payload['email'] ?? '');
        $plan = (string) ($payload['plan'] ?? 'free');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ApiResponse::json(['error' => 'invalid_email'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::json(($onboarding ?? new ClientOnboardingService())->register($email, $plan, (string) ($payload['password'] ?? '')), Response::HTTP_CREATED);
    }

    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(Request $request, JwtService $jwt): Response
    {
        $payload = $this->payload($request);
        $email = (string) ($payload['email'] ?? '');
        $password = (string) ($payload['password'] ?? '');
        $user = new FixtureRepository()->userByEmail($email);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $user === null || !password_verify($password, (string) $user['password_hash'])) {
            return ApiResponse::json(['error' => 'invalid_credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $secret = (string) ($_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? $_ENV['APP_SECRET'] ?? 'dev-secret');

        return ApiResponse::json([
            'token' => $jwt->issue($email, $user['roles'], $secret),
            'type' => 'Bearer',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'roles' => $user['roles'],
                'plan' => $user['plan'],
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(Request $request): array
    {
        if ($request->getContentTypeFormat() === 'json') {
            return json_decode($request->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        }

        return $request->request->all();
    }
}
