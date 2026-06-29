<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthController
{
    public function login(Request $request): Response
    {
        return ApiResponse::json(['token' => 'jwt-issued-by-symfony-security-in-production', 'type' => 'Bearer']);
    }
}
