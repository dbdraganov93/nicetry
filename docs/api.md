# API Specification and Usage Guide

Base URL examples use `https://api.example.com`. All control-plane endpoints except `/healthz` require an API key:

```bash
-H 'Authorization: Bearer gp_live_your_key'
```

For local development with the scaffold, any bearer token beginning with `gp_` is accepted so the API shape can be exercised before wiring persistent authentication.

## Plans

### `GET /v1/plans`

Returns commercial plans with request, bandwidth, country, and concurrency limits.

```bash
curl -H 'Authorization: Bearer gp_test' https://api.example.com/v1/plans
```

Example response:

```json
{
  "plans": [
    {
      "code": "starter",
      "name": "Starter",
      "price_cents": 2900,
      "monthly_request_limit": 250000,
      "monthly_bandwidth_limit_gb": 50,
      "monthly_bandwidth_limit_bytes": 53687091200,
      "concurrent_connections": 50,
      "countries": ["DE", "FR", "NL", "US"],
      "features": ["email_support", "api_keys"]
    }
  ]
}
```

## Countries

### `GET /v1/countries`

Returns enabled countries, cities, and current public exit IPs.

```bash
curl -H 'Authorization: Bearer gp_test' https://api.example.com/v1/countries
```

## Usage

### `GET /v1/usage`

Returns current billing-period usage, plan limits, percent consumed, countries used, error counts, and latency.

```bash
curl -H 'Authorization: Bearer gp_test' -H 'X-User-Id: demo-user' https://api.example.com/v1/usage
```

## API Keys

### `POST /v1/api-keys`

Creates an API key and returns the secret once.

```bash
curl -X POST -H 'Authorization: Bearer gp_test' https://api.example.com/v1/api-keys
```

### `DELETE /v1/api-keys/{id}`

Revokes an API key.

```bash
curl -X DELETE -H 'Authorization: Bearer gp_test' https://api.example.com/v1/api-keys/00000000-0000-0000-0000-000000000000
```

## Proxy Credentials

### `POST /v1/proxy-credentials`

Creates country-scoped proxy credentials.

```bash
curl -X POST \
  -H 'Authorization: Bearer gp_test' \
  -H 'X-User-Id: customer123' \
  -d 'country=DE' \
  https://api.example.com/v1/proxy-credentials
```

Then configure your HTTP client:

```bash
curl -x http://de.customer123:generated-password@proxy.example.com:3128 https://ifconfig.me
```

The country prefix in `de.customer123` routes the request to the German exit-node pool. HTTPS destinations use standard HTTP `CONNECT` tunneling through the proxy.

## Monitoring

### `GET /v1/monitoring/nodes`

Returns node status, load percentage, latency, public IP, active connections, and VPN uptime.

### `GET /v1/monitoring/dashboard`

Returns the customer usage summary plus node health data suitable for dashboard cards.

## Error model

```json
{"error":"unauthorized","message":"Use Authorization: Bearer gp_<token>"}
```

Common status codes: `401` unauthorized, `404` not found, `429` rate limited when Redis-backed limiters are enabled, and `503` no healthy exit node.
