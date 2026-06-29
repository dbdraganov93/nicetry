<?php

declare(strict_types=1);

use GeoProxy\Controller\ApiKeyController;
use GeoProxy\Controller\CountryController;
use GeoProxy\Controller\HealthController;
use GeoProxy\Controller\UsageController;
use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

$request = Request::createFromGlobals();
$path = $request->getPathInfo();
$method = $request->getMethod();

$routes = [
    ['GET', '/healthz', [HealthController::class, 'health']],
    ['GET', '/v1/countries', [CountryController::class, 'list']],
    ['GET', '/v1/usage', [UsageController::class, 'currentUser']],
    ['POST', '/v1/api-keys', [ApiKeyController::class, 'create']],
];

foreach ($routes as [$routeMethod, $routePath, $handler]) {
    if ($method === $routeMethod && $path === $routePath) {
        $controller = new $handler[0]();
        $controller->{$handler[1]}($request)->send();
        return;
    }
}

if ($method === 'DELETE' && preg_match('#^/v1/api-keys/[a-f0-9-]+$#', $path)) {
    (new ApiKeyController())->delete($request)->send();
    return;
}

ApiResponse::json(['error' => 'not_found'], 404)->send();
