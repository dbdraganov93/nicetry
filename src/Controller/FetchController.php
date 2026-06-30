<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use GeoProxy\Service\NordVpnFetchService;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FetchController
{
    #[Route('/v1/fetch', name: 'geo_fetch', methods: ['POST'])]
    public function fetch(Request $request, NordVpnFetchService $fetcher): Response
    {
        $payload = $this->payload($request);
        $url = (string) ($payload['url'] ?? '');
        $country = (string) ($payload['country'] ?? '');
        $timeout = (int) ($payload['timeout_seconds'] ?? 45);
        $responseMode = strtolower((string) ($payload['response'] ?? 'raw'));

        try {
            $result = $fetcher->fetch($url, $country, $timeout);
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (RuntimeException $exception) {
            return ApiResponse::json(['error' => 'nordvpn_fetch_failed', 'detail' => $exception->getMessage()], Response::HTTP_BAD_GATEWAY);
        }

        if ($responseMode === 'envelope') {
            return ApiResponse::json($result);
        }

        return new Response($result['body'], Response::HTTP_OK, array_filter([
            'Content-Type' => $result['content_type'] ?? 'text/plain; charset=UTF-8',
            'X-GeoProxy-Origin-Status' => (string) $result['status'],
            'X-GeoProxy-Country' => $result['country'],
            'X-GeoProxy-Url' => $result['url'],
        ]));
    }

    /** @return array<string, mixed> */
    private function payload(Request $request): array
    {
        if ($request->getContentTypeFormat() === 'json') {
            $decoded = json_decode($request->getContent() ?: '{}', true);

            return is_array($decoded) ? $decoded : [];
        }

        return $request->request->all();
    }
}
