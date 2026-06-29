# Geo Proxy SaaS Platform

Symfony 7 / PHP 8.4 foundation for a VPN-backed HTTP/HTTPS geo proxy SaaS. The repository now contains the runnable application shell, Doctrine persistence model, authentication/rate-limit scaffolding, node-management endpoints, billing/monitoring placeholders, Docker Compose, CI, and Kubernetes deployment manifests.

## Local development

1. Copy `.env.example` to `.env` and adjust secrets.
2. Run `make up` to build and start PHP-FPM, Nginx, Postgres, Redis, Prometheus, Loki, Grafana, and a sample VPN/proxy node pair.
3. Visit `http://localhost:8080/healthz` for health and `http://localhost:8080/metrics` for Prometheus text metrics.

## Architecture decisions

- **Symfony components with Doctrine attributes**: entities live in `src/Entity` and repositories in `src/Repository` so business logic can later move into services without coupling controllers to persistence.
- **UUID primary keys**: public-facing records use UUIDs to avoid exposing sequence cardinality and to support multi-region writes.
- **Postgres + Redis**: Postgres stores transactional identity, billing, nodes, and usage data; Redis is reserved for rate limiting, sticky-session state, and fast routing metadata.
- **API key + JWT authentication**: API keys secure programmatic proxy/API access, while JWT supports user login and the Vue admin dashboard.
- **Rate limiting at the app layer**: Symfony RateLimiter configuration allows per-identity quotas before expensive routing or billing work begins.
- **Health-aware least-loaded routing**: the routing engine filters by country and health, then chooses the lowest active connection count with weight as a tie breaker.
- **Observability first**: `/metrics`, Prometheus, Grafana, and Loki are included from day one so routing and node issues are measurable.
- **Docker Compose mirrors production concerns**: separate API, Nginx, worker, Postgres, Redis, VPN, proxy, and monitoring services keep responsibilities isolated.
- **Kubernetes manifests**: deployments include rolling updates, probes, HPA, and backup cron jobs for a production path.

## Layer overview

- Controllers expose HTTP/auth/node/admin/billing/health boundaries and intentionally contain no business logic yet.
- Services contain reusable behaviors such as routing, JWT creation/verification, node health, rate limiting, and billing payload construction.
- Doctrine entities model users, API keys, plans, subscriptions, proxy credentials, countries/cities, exit/proxy nodes, usage windows, and request logs.
- Migrations create indexes for login lookup, API-key lookup, country/city mapping, node health/load routing, usage windows, and request-log analytics.
