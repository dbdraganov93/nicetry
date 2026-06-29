# GeoProxy API Documentation

GeoProxy exposes JSON management APIs for authentication, plan discovery, account usage, API-key management, node operations, billing, and health checks. Proxy traffic itself is authenticated separately through generated HTTP proxy credentials.

## Base URLs

| Environment | URL |
| --- | --- |
| Local API | `http://localhost:8080` |
| Kubernetes ingress | Configure from the host attached to `k8s/api-deployment.yaml` |

All JSON endpoints return `Content-Type: application/json` unless otherwise noted.

## Authentication

### User session JWT

Use the auth endpoints to create an account or sign in. Login returns a JWT bearer token for user-facing dashboards.

```bash
curl -s http://localhost:8080/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"user@example.com","password":"secret"}'
```

### Management API key

Management endpoints are designed to accept an API key header:

```http
Authorization: Bearer gp_live_your_secret_key
```

Create keys with `POST /v1/api-keys`. Store the returned `secret` immediately because it is intended to be shown once.

### Proxy credentials

HTTP/HTTPS proxy traffic uses basic authentication against the proxy layer, not the JSON API:

```text
username: de.customer123
password: generated-proxy-password
```

The username prefix selects routing geography. Future compatible forms include city routing such as `de-berlin.customer123`, sticky-session suffixes, and dedicated-IP aliases.

## Error format

Validation and authentication errors use compact machine-readable codes:

```json
{
  "error": "invalid_email"
}
```

Clients should branch on `error` rather than localized copy.

## Client examples

The examples below show the same read-only management request in several languages. Set `GEOPROXY_API_KEY` to a management API key created with `POST /v1/api-keys`, and change `GEOPROXY_BASE_URL` when calling a deployed environment instead of the local API.

### cURL

```bash
export GEOPROXY_BASE_URL="http://localhost:8080"
export GEOPROXY_API_KEY="gp_live_your_secret_key"

curl -s "$GEOPROXY_BASE_URL/v1/countries" \
  -H "Authorization: Bearer $GEOPROXY_API_KEY" \
  -H 'Accept: application/json'
```

### JavaScript / Node.js

```javascript
const baseUrl = process.env.GEOPROXY_BASE_URL ?? 'http://localhost:8080';
const apiKey = process.env.GEOPROXY_API_KEY;

const response = await fetch(`${baseUrl}/v1/countries`, {
  headers: {
    Authorization: `Bearer ${apiKey}`,
    Accept: 'application/json',
  },
});

if (!response.ok) {
  throw new Error(`GeoProxy API returned ${response.status}`);
}

const countries = await response.json();
console.log(countries);
```

### Python

```python
import os
import requests

base_url = os.getenv("GEOPROXY_BASE_URL", "http://localhost:8080")
api_key = os.environ["GEOPROXY_API_KEY"]

response = requests.get(
    f"{base_url}/v1/countries",
    headers={
        "Authorization": f"Bearer {api_key}",
        "Accept": "application/json",
    },
    timeout=10,
)
response.raise_for_status()

print(response.json())
```

### PHP

```php
<?php

$baseUrl = getenv('GEOPROXY_BASE_URL') ?: 'http://localhost:8080';
$apiKey = getenv('GEOPROXY_API_KEY');

$ch = curl_init($baseUrl . '/v1/countries');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json',
    ],
]);

$body = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

if ($status < 200 || $status >= 300) {
    throw new RuntimeException('GeoProxy API returned HTTP ' . $status);
}

print_r(json_decode($body, true, flags: JSON_THROW_ON_ERROR));
```

### Go

```go
package main

import (
    "encoding/json"
    "fmt"
    "net/http"
    "os"
    "time"
)

func main() {
    baseURL := os.Getenv("GEOPROXY_BASE_URL")
    if baseURL == "" {
        baseURL = "http://localhost:8080"
    }

    req, err := http.NewRequest(http.MethodGet, baseURL+"/v1/countries", nil)
    if err != nil {
        panic(err)
    }
    req.Header.Set("Authorization", "Bearer "+os.Getenv("GEOPROXY_API_KEY"))
    req.Header.Set("Accept", "application/json")

    client := &http.Client{Timeout: 10 * time.Second}
    res, err := client.Do(req)
    if err != nil {
        panic(err)
    }
    defer res.Body.Close()

    if res.StatusCode < 200 || res.StatusCode >= 300 {
        panic(fmt.Sprintf("GeoProxy API returned HTTP %d", res.StatusCode))
    }

    var countries []map[string]any
    if err := json.NewDecoder(res.Body).Decode(&countries); err != nil {
        panic(err)
    }

    fmt.Printf("%+v\n", countries)
}
```

### Proxy traffic examples by website and country

After creating proxy credentials, configure an HTTP client to use the proxy endpoint and basic-auth username/password. The username controls geography; for example, `de.customer123` routes through Germany.

The examples below request a caller-provided website through a caller-provided country. They use these shared inputs:

| Input | Example value | Notes |
| --- | --- | --- |
| Website | `https://ifconfig.me/ip` | Any `http://` or `https://` URL your plan may access. |
| Country | `DE` | Two-letter country code from `GET /v1/countries`. |
| Customer ID | `customer123` | The suffix in your generated proxy username. |
| Proxy password | `generated-proxy-password` | The password returned when creating proxy credentials. |
| Proxy host | `proxy.local:3128` | Replace with your deployed proxy hostname and port. |

#### cURL

```bash
WEBSITE="https://ifconfig.me/ip"
COUNTRY="DE"
CUSTOMER_ID="customer123"
PROXY_PASSWORD="generated-proxy-password"
PROXY_HOST="proxy.local:3128"

COUNTRY_LOWER=$(printf '%s' "$COUNTRY" | tr '[:upper:]' '[:lower:]')

curl -s \
  -x "http://${COUNTRY_LOWER}.${CUSTOMER_ID}:${PROXY_PASSWORD}@${PROXY_HOST}" \
  "$WEBSITE"
```

Example output:

```text
203.0.113.42
```

#### Python

```python
import os
import requests

website = os.getenv("GEOPROXY_WEBSITE", "https://ifconfig.me/ip")
country = os.getenv("GEOPROXY_COUNTRY", "DE").lower()
customer_id = os.getenv("GEOPROXY_CUSTOMER_ID", "customer123")
proxy_password = os.environ["GEOPROXY_PROXY_PASSWORD"]
proxy_host = os.getenv("GEOPROXY_PROXY_HOST", "proxy.local:3128")

proxy_url = f"http://{country}.{customer_id}:{proxy_password}@{proxy_host}"

response = requests.get(
    website,
    proxies={"http": proxy_url, "https": proxy_url},
    timeout=20,
)
response.raise_for_status()

print(response.text.strip())
```

Example output:

```text
203.0.113.42
```

#### PHP

```php
<?php

$website = getenv('GEOPROXY_WEBSITE') ?: 'https://ifconfig.me/ip';
$country = strtolower(getenv('GEOPROXY_COUNTRY') ?: 'DE');
$customerId = getenv('GEOPROXY_CUSTOMER_ID') ?: 'customer123';
$proxyPassword = getenv('GEOPROXY_PROXY_PASSWORD');
$proxyHost = getenv('GEOPROXY_PROXY_HOST') ?: 'proxy.local:3128';

$ch = curl_init($website);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_PROXY => 'http://' . $proxyHost,
    CURLOPT_PROXYUSERPWD => $country . '.' . $customerId . ':' . $proxyPassword,
    CURLOPT_TIMEOUT => 20,
]);

$body = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($body === false || $status < 200 || $status >= 300) {
    throw new RuntimeException($error ?: 'GeoProxy request returned HTTP ' . $status);
}

echo trim($body) . PHP_EOL;
```

Example output:

```text
203.0.113.42
```

#### JavaScript / Node.js

Install a proxy-capable fetch dispatcher first:

```bash
npm install undici
```

```javascript
import { fetch, ProxyAgent } from 'undici';

const website = process.env.GEOPROXY_WEBSITE ?? 'https://ifconfig.me/ip';
const country = (process.env.GEOPROXY_COUNTRY ?? 'DE').toLowerCase();
const customerId = process.env.GEOPROXY_CUSTOMER_ID ?? 'customer123';
const proxyPassword = process.env.GEOPROXY_PROXY_PASSWORD;
const proxyHost = process.env.GEOPROXY_PROXY_HOST ?? 'proxy.local:3128';

const proxyUrl = `http://${country}.${customerId}:${proxyPassword}@${proxyHost}`;
const dispatcher = new ProxyAgent(proxyUrl);

const response = await fetch(website, { dispatcher });

if (!response.ok) {
  throw new Error(`GeoProxy request returned HTTP ${response.status}`);
}

console.log((await response.text()).trim());
```

Example output:

```text
203.0.113.42
```

## Endpoints

### `POST /auth/register`

Creates a user account placeholder and assigns a requested plan.

**Request**

```json
{
  "email": "user@example.com",
  "password": "secret",
  "plan": "starter"
}
```

**Response `201`**

```json
{
  "status": "registered",
  "email": "user@example.com",
  "plan": "starter"
}
```

**Errors**

| Status | Code | Meaning |
| --- | --- | --- |
| `422` | `invalid_email` | Email is missing or malformed. |

### `POST /auth/login`

Authenticates a user and returns a bearer JWT.

**Request**

```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

**Response `200`**

```json
{
  "token": "eyJ...",
  "type": "Bearer"
}
```

**Errors**

| Status | Code | Meaning |
| --- | --- | --- |
| `401` | `invalid_credentials` | Credentials are invalid or incomplete. |

### `GET /v1/plans`

Returns the public subscription catalog.

```bash
curl -s http://localhost:8080/v1/plans
```

**Response `200`**

```json
{
  "plans": [
    {
      "code": "starter",
      "name": "Starter",
      "price_cents": 2900,
      "monthly_request_limit": 100000,
      "monthly_bandwidth_limit_gb": 50,
      "features": ["shared_pool"]
    }
  ]
}
```

### `GET /v1/countries`

Lists enabled countries and cities available for geo routing.

```bash
curl -s http://localhost:8080/v1/countries \
  -H 'Authorization: Bearer gp_live_your_secret_key'
```

**Response `200`**

```json
[
  {"country":"Germany","code":"DE","cities":["Berlin","Hamburg"]},
  {"country":"France","code":"FR","cities":["Paris","Marseille"]}
]
```

### `GET /v1/usage`

Returns usage for the authenticated user's current billing period.

```bash
curl -s http://localhost:8080/v1/usage \
  -H 'Authorization: Bearer gp_live_your_secret_key'
```

**Response `200`**

```json
{
  "period": "2026-06",
  "requests": 0,
  "bytes_in": 0,
  "bytes_out": 0,
  "countries": [],
  "errors": 0
}
```

### `POST /v1/api-keys`

Creates a new management API key.

```bash
curl -s -X POST http://localhost:8080/v1/api-keys \
  -H 'Authorization: Bearer existing_key'
```

**Response `201`**

```json
{
  "id": "7f1d4d6b0d7f4e5ab8d7f4e5ab8d7f4e",
  "prefix": "gp_ab12cd34",
  "secret": "gp_live_generated_secret",
  "created_at": "2026-06-29T12:00:00+00:00"
}
```

### `DELETE /v1/api-keys/{id}`

Revokes an API key by ID.

```bash
curl -s -X DELETE http://localhost:8080/v1/api-keys/7f1d4d6b0d7f4e5ab8d7f4e5ab8d7f4e \
  -H 'Authorization: Bearer gp_live_your_secret_key'
```

**Response `200`**

```json
{"deleted": true}
```

### `GET /v1/admin/dashboard`

Returns aggregate administrative metrics for internal dashboards.

```bash
curl -s http://localhost:8080/v1/admin/dashboard \
  -H 'Authorization: Bearer gp_live_admin_key'
```

**Response `200`**

```json
{
  "users": 0,
  "nodes": 0,
  "usage_bytes": 0,
  "billing_mrr_cents": 0
}
```

### `GET /v1/billing/plans`

Returns billing-provider plan mappings. The current scaffold returns an empty collection until a billing provider is connected.

**Response `200`**

```json
{"plans": []}
```

### `GET /v1/billing/invoices`

Returns account invoice summaries. The current scaffold returns an empty collection until billing storage is connected.

**Response `200`**

```json
{"invoices": []}
```

### `POST /webhooks/stripe`

Receives Stripe webhook events.

**Response `200`**

```json
{"received": true}
```

### `POST /v1/nodes/register`

Registers a proxy or exit node with the control plane.

**Response `201`**

```json
{"status": "registered"}
```

### `POST /v1/nodes/heartbeat`

Updates node health, capacity, and load telemetry.

**Response `200`**

```json
{"status": "healthy"}
```

### `POST /v1/nodes/public-ip`

Verifies the node's public egress IP address.

**Response `200`**

```json
{"public_ip_verified": true}
```

### `GET /healthz`

Lightweight health check for load balancers and probes.

```bash
curl -s http://localhost:8080/healthz
```

**Response `200`**

```json
{"status": "ok", "service": "geo-proxy-gateway"}
```

### `GET /metrics`

Prometheus scrape endpoint. This endpoint returns `text/plain`.

```bash
curl -s http://localhost:8080/metrics
```

**Response `200`**

```text
geo_proxy_up 1
```

## Frontend views

The web application has separate professional views instead of a single combined page:

| View | Path | Purpose |
| --- | --- | --- |
| Home | `/` | Product positioning and entry points. |
| Login | `/login` | Customer sign-in form posting to `/auth/login`. |
| Register | `/register` | Account creation form posting to `/auth/register`. |
| Plans | `/plans` | Plan catalog from the same catalog used by `/v1/plans`. |
| Admin | `/admin` | Operations dashboard with coverage and node-health data. |

## Quick local smoke test

```bash
php -S 127.0.0.1:8080 -t public
curl -s http://127.0.0.1:8080/healthz
curl -s http://127.0.0.1:8080/v1/plans
```
