<?php

declare(strict_types=1);

use GeoProxy\Controller\AdminController;
use GeoProxy\Controller\ApiKeyController;
use GeoProxy\Controller\AuthController;
use GeoProxy\Controller\BillingController;
use GeoProxy\Controller\CountryController;
use GeoProxy\Controller\HealthController;
use GeoProxy\Controller\NodeController;
use GeoProxy\Controller\UsageController;
use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

$request = Request::createFromGlobals();
$path = $request->getPathInfo();
$method = $request->getMethod();

$routes = [
    ['GET', '/healthz', [HealthController::class, 'health']],
    ['GET', '/metrics', [HealthController::class, 'metrics']],
    ['POST', '/auth/login', [AuthController::class, 'login']],
    ['GET', '/v1/countries', [CountryController::class, 'list']],
    ['GET', '/v1/usage', [UsageController::class, 'currentUser']],
    ['POST', '/v1/api-keys', [ApiKeyController::class, 'create']],
    ['POST', '/v1/nodes/register', [NodeController::class, 'register']],
    ['POST', '/v1/nodes/heartbeat', [NodeController::class, 'heartbeat']],
    ['POST', '/v1/nodes/public-ip', [NodeController::class, 'publicIp']],
    ['GET', '/v1/admin/dashboard', [AdminController::class, 'dashboard']],
    ['GET', '/v1/billing/plans', [BillingController::class, 'plans']],
    ['GET', '/v1/billing/invoices', [BillingController::class, 'invoices']],
    ['POST', '/webhooks/stripe', [BillingController::class, 'stripeWebhook']],
];

foreach ($routes as [$routeMethod, $routePath, $handler]) {
    if ($method === $routeMethod && $path === $routePath) {
        new $handler[0]()->{$handler[1]}($request)->send();
        return;
    }
}

if ($method === 'DELETE' && preg_match('#^/v1/api-keys/[a-f0-9-]+$#', $path)) {
    new ApiKeyController()->delete($request)->send();
    return;
}

ApiResponse::json(['error' => 'not_found'], 404)->send();
