# Monitoring

Every minute the health worker should verify public IP, VPN tunnel status, DNS resolution, latency, and proxy availability. Unhealthy nodes are marked unavailable and removed from routing.

## Metrics

- Gateway request count and latency.
- Proxy connection count.
- Bytes in/out by user and country.
- Node availability and VPN uptime.
- Error rate by destination and country.
- Billing usage rollups.

## Alerts

- No healthy nodes in a country.
- VPN public IP mismatch.
- Elevated 5xx or CONNECT failure rate.
- Redis/PostgreSQL unavailable.
- Bandwidth approaching plan or provider limits.
