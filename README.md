# Geo Proxy SaaS Platform

Symfony 7 / PHP 8.4 foundation for a NordVPN CLI-backed HTTP/HTTPS geo fetch and proxy SaaS. The repository now contains the runnable application shell, Doctrine persistence model, authentication/rate-limit scaffolding, node-management endpoints, billing/monitoring placeholders, Docker Compose, CI, and Kubernetes deployment manifests.

## Local development

1. Copy `.env.example` to `.env` and adjust secrets.
2. Run `make up` to build and start PHP-FPM, Nginx, Postgres, Redis, Prometheus, Loki, Grafana, and a NordVPN-enabled gateway container.
3. Visit `http://localhost:8080/healthz` for health and `http://localhost:8080/metrics` for Prometheus text metrics.


## Demo logins

After starting the local stack, use these seeded accounts to exercise the real login and admin flows:

| Area | Link | Email | Password | Role | Plan |
| --- | --- | --- | --- | --- | --- |
| Admin dashboard | `http://localhost:8080/admin` | `admin@geoproxy.test` | `AdminPass123!` | Admin + user | Enterprise |
| User dashboard/login | `http://localhost:8080/login` | `user@geoproxy.test` | `UserPass123!` | User | Starter |
| User dashboard/login | `http://localhost:8080/login` | `maya@geoproxy.test` | `MayaPass123!` | User | Pro |

The same credentials work with the JSON login endpoint:

```bash
curl -s http://localhost:8080/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"user@geoproxy.test","password":"UserPass123!"}'
```

## Architecture decisions

- **Symfony components with Doctrine attributes**: entities live in `src/Entity` and repositories in `src/Repository` so business logic can later move into services without coupling controllers to persistence.
- **UUID primary keys**: public-facing records use UUIDs to avoid exposing sequence cardinality and to support multi-region writes.
- **Postgres + Redis**: Postgres stores transactional identity, billing, nodes, and usage data; Redis is reserved for rate limiting, sticky-session state, and fast routing metadata.
- **API key + JWT authentication**: API keys secure programmatic proxy/API access, while JWT supports user login and the Vue admin dashboard.
- **Rate limiting at the app layer**: Symfony RateLimiter configuration allows per-identity quotas before expensive routing or billing work begins.
- **Health-aware least-loaded routing**: the routing engine filters by country and health, then chooses the lowest active connection count with weight as a tie breaker.
- **Observability first**: `/metrics`, Prometheus, Grafana, and Loki are included from day one so routing and node issues are measurable.
- **Docker Compose mirrors production concerns**: separate API, Nginx, worker, Postgres, Redis, NordVPN-enabled gateway, and monitoring services keep responsibilities isolated.
- **Kubernetes manifests**: deployments include rolling updates, probes, HPA, and backup cron jobs for a production path.

## Layer overview

- Controllers expose HTTP/auth/node/admin/billing/health boundaries and intentionally contain no business logic yet.
- Services contain reusable behaviors such as routing, JWT creation/verification, node health, rate limiting, and billing payload construction.
- Doctrine entities model users, API keys, plans, subscriptions, proxy credentials, countries/cities, exit/proxy nodes, usage windows, and request logs.
- Migrations create indexes for login lookup, API-key lookup, country/city mapping, node health/load routing, usage windows, and request-log analytics.

## NordVPN country fetch API

`POST /v1/fetch` accepts a public `url` plus a NordVPN `country`/location name, connects the gateway container with `nordvpn connect <country>`, and returns the raw origin response body. Send `response: "envelope"` to receive JSON metadata and body together. The endpoint intentionally uses the NordVPN CLI from inside the container instead of requiring API users to manage WireGuard or OpenVPN profiles.
