# Deployment Guide

## Local Docker Compose

```bash
cp .env.example .env
docker compose up --build
```

## Production recommendations

- Terminate TLS at a managed load balancer or hardened NGINX tier.
- Run gateway replicas behind the load balancer.
- Use managed PostgreSQL with PITR and read replicas.
- Use managed Redis with persistence for counters and sticky-session routing.
- Keep VPN credentials in a secrets manager.
- Pin provider images and scan container images in CI.
- Isolate each country pool with network policies.

## Kubernetes path

Convert each VPN/proxy pair into a pod with a shared network namespace. Use horizontal pod autoscaling for the gateway and workers, and node labels/taints for geographically constrained egress infrastructure.
