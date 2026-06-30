<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use InvalidArgumentException;
use RuntimeException;

final class NordVpnFetchService
{
    public function __construct(private readonly CommandRunner $commands) {}

    /** @return array{country:string,url:string,status:int,content_type:string|null,body:string} */
    public function fetch(string $url, string $country, int $timeoutSeconds = 45): array
    {
        $url = trim($url);
        $country = $this->normalizeCountry($country);
        $timeoutSeconds = max(5, min(120, $timeoutSeconds));

        if (!$this->isAllowedUrl($url)) {
            throw new InvalidArgumentException('url_must_be_public_http_or_https');
        }

        $nordvpn = (string) ($_ENV['NORDVPN_COMMAND'] ?? $_SERVER['NORDVPN_COMMAND'] ?? 'nordvpn');
        $curl = (string) ($_ENV['NORDVPN_CURL_COMMAND'] ?? $_SERVER['NORDVPN_CURL_COMMAND'] ?? 'curl');

        $connect = $this->commands->run([$nordvpn, 'connect', $country], $timeoutSeconds);
        if ($connect['exit_code'] !== 0) {
            throw new RuntimeException('nordvpn_connect_failed: ' . $connect['output']);
        }

        $curlResult = $this->commands->run([
            $curl,
            '--silent',
            '--show-error',
            '--location',
            '--max-time',
            (string) $timeoutSeconds,
            '--write-out',
            "\n__GEOPROXY_HTTP_STATUS__:%{http_code}\n__GEOPROXY_CONTENT_TYPE__:%{content_type}",
            $url,
        ], $timeoutSeconds + 5);

        if ($curlResult['exit_code'] !== 0) {
            throw new RuntimeException('origin_fetch_failed: ' . $curlResult['output']);
        }

        return $this->parseCurlOutput($curlResult['output'], $url, $country);
    }

    private function normalizeCountry(string $country): string
    {
        $country = trim($country);
        if ($country === '' || !preg_match('/^[A-Za-z][A-Za-z _-]{1,63}$/', $country)) {
            throw new InvalidArgumentException('invalid_country');
        }

        return str_replace(' ', '_', $country);
    }

    private function isAllowedUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])) {
            return false;
        }

        $host = strtolower((string) $parts['host']);
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /** @return array{country:string,url:string,status:int,content_type:string|null,body:string} */
    private function parseCurlOutput(string $output, string $url, string $country): array
    {
        $statusMarker = "\n__GEOPROXY_HTTP_STATUS__:";
        $contentTypeMarker = "\n__GEOPROXY_CONTENT_TYPE__:";
        $statusPos = strrpos($output, $statusMarker);
        $contentTypePos = strrpos($output, $contentTypeMarker);

        if ($statusPos === false || $contentTypePos === false || $contentTypePos < $statusPos) {
            throw new RuntimeException('origin_fetch_missing_metadata');
        }

        $body = substr($output, 0, $statusPos);
        $status = (int) trim(substr($output, $statusPos + strlen($statusMarker), $contentTypePos - ($statusPos + strlen($statusMarker))));
        $contentType = trim(substr($output, $contentTypePos + strlen($contentTypeMarker)));

        return [
            'country' => $country,
            'url' => $url,
            'status' => $status,
            'content_type' => $contentType === '' ? null : $contentType,
            'body' => $body,
        ];
    }
}
