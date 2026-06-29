# Geo Proxy SaaS Platform

Production-oriented foundation for a VPN-backed HTTP/HTTPS geo proxy SaaS. It includes a Symfony-style PHP API gateway, plan and usage monitoring APIs, PostgreSQL schema, Redis-backed routing/rate-limit design, Docker Compose infrastructure, VPN/proxy node templates, monitoring, CI, and operational documentation.

## Capabilities

- API key authentication for management APIs.
- Commercial plan catalog with Free, Starter, Pro, and Enterprise tiers.
- Per-user usage monitoring for requests, bandwidth, connection time, country distribution, errors, and latency.
- Proxy credential model using usernames like `de.customer123` for country routing.
- Least-loaded exit-node selection design with sticky-session extension points.
- Usage accounting for requests, bytes, latency, errors, and countries.
- Health worker for VPN/proxy node checks and automatic node disablement.
- Stripe-ready billing data model and service abstraction.
- Prometheus/Grafana/Loki-ready monitoring plan.

## Quick start

```bash
cp .env.example .env
docker compose up --build
```

API gateway: `http://localhost:8080`.

Try local API calls after dependencies are installed:

```bash
curl http://localhost:8080/healthz
curl -H 'Authorization: Bearer gp_test' http://localhost:8080/v1/plans
curl -H 'Authorization: Bearer gp_test' -H 'X-User-Id: demo-user' http://localhost:8080/v1/usage
curl -H 'Authorization: Bearer gp_test' http://localhost:8080/v1/monitoring/dashboard
```

## Documentation

- [Architecture](docs/architecture.md)
- [API specification and usage guide](docs/api.md)
- [Database schema](docs/database.md)
- [Deployment guide](docs/deployment.md)
- [Monitoring](docs/monitoring.md)
- [Testing strategy](docs/testing.md)
- [Disaster recovery](docs/disaster-recovery.md)
