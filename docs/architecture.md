# Architecture

```mermaid
flowchart TD
  Internet((Internet)) --> NGINX[NGINX TLS Edge]
  NGINX --> Gateway[Stateless Symfony Gateway]
  Gateway --> Auth[Authentication, Rate Limits, Billing Metrics]
  Auth --> Redis[(Redis)]
  Auth --> Postgres[(PostgreSQL)]
  Gateway --> Router[Routing Engine]
  Router --> DE[Germany Exit Pool]
  Router --> FR[France Exit Pool]
  DE --> VPNDE[Gluetun WireGuard VPN]
  VPNDE --> ProxyDE[3proxy HTTP/HTTPS CONNECT]
  FR --> VPNFR[Gluetun WireGuard VPN]
  VPNFR --> ProxyFR[3proxy HTTP/HTTPS CONNECT]
  ProxyDE --> Target[Target Websites]
  ProxyFR --> Target
  Worker[Health Worker] --> DE
  Worker --> FR
  Worker --> Prometheus[Prometheus]
  Prometheus --> Grafana[Grafana]
```

## Request flow

1. Customer authenticates to the proxy with a username such as `de.customer123`.
2. Gateway validates credentials, plan limits, and rate limits.
3. Routing engine extracts country/city/sticky-session hints.
4. Redis stores live node load and sticky mappings.
5. The least-loaded healthy node is selected.
6. Traffic is forwarded to 3proxy sharing the network namespace of its VPN container.
7. Usage events are written asynchronously for billing and analytics.

## Modularity

- Gateway: stateless API and control plane.
- Worker: health checks, usage aggregation, Stripe synchronization.
- Exit pools: independently scaled VPN/proxy pairs per geography.
- Monitoring: Prometheus metrics, Grafana dashboards, Loki logs.
