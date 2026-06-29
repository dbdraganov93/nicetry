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
