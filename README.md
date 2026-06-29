# Geo Proxy SaaS Platform

Production-oriented foundation for a VPN-backed HTTP/HTTPS geo proxy SaaS. It includes a Symfony-style PHP API gateway, PostgreSQL schema, Redis-backed routing/rate-limit design, Docker Compose infrastructure, VPN/proxy node templates, monitoring, CI, and operational documentation.

## Capabilities

- API key authentication for management APIs.
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

## Documentation

- [Architecture](docs/architecture.md)
- [API specification](docs/api.md)
- [Database schema](docs/database.md)
- [Deployment guide](docs/deployment.md)
- [Monitoring](docs/monitoring.md)
- [Testing strategy](docs/testing.md)
- [Disaster recovery](docs/disaster-recovery.md)
