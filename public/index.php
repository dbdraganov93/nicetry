<?php

declare(strict_types=1);

use GeoProxy\Controller\ApiKeyController;
use GeoProxy\Controller\CountryController;
use GeoProxy\Controller\HealthController;
use GeoProxy\Controller\MonitoringController;
use GeoProxy\Controller\PlanController;
use GeoProxy\Controller\ProxyCredentialController;
use GeoProxy\Controller\UsageController;
use GeoProxy\Security\ApiKeyAuthenticator;
use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

$request = Request::createFromGlobals();
$path = $request->getPathInfo();
$method = $request->getMethod();
$publicPaths = ['/healthz'];

if (!in_array($path, $publicPaths, true) && !new ApiKeyAuthenticator()->isHeaderPresent($request->headers->get('Authorization'))) {
    ApiResponse::json(['error' => 'unauthorized', 'message' => 'Use Authorization: Bearer gp_<token>'], 401)->send();
    return;
}

$routes = [
    ['GET', '/healthz', [HealthController::class, 'health']],
    ['GET', '/v1/countries', [CountryController::class, 'list']],
    ['GET', '/v1/plans', [PlanController::class, 'list']],
    ['GET', '/v1/usage', [UsageController::class, 'currentUser']],
    ['GET', '/v1/monitoring/nodes', [MonitoringController::class, 'nodes']],
    ['GET', '/v1/monitoring/dashboard', [MonitoringController::class, 'dashboard']],
    ['POST', '/v1/api-keys', [ApiKeyController::class, 'create']],
    ['POST', '/v1/proxy-credentials', [ProxyCredentialController::class, 'create']],
];

foreach ($routes as [$routeMethod, $routePath, $handler]) {
    if ($method === $routeMethod && $path === $routePath) {
        $controller = new $handler[0]();
        $controller->{$handler[1]}($request)->send();
        return;
    }
}

if ($method === 'DELETE' && preg_match('#^/v1/api-keys/[a-f0-9-]+$#', $path)) {
    new ApiKeyController()->delete($request)->send();
    return;
}

ApiResponse::json(['error' => 'not_found'], 404)->send();
