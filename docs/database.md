# Database Schema

The initial PostgreSQL schema is in `migrations/001_initial_schema.sql` and includes users, plans, subscriptions, API keys, proxy credentials, countries, cities, VPN nodes, proxy nodes, raw usage events, usage rollups, sessions, invoices, and payments.

## Plans and usage

The `plans` table stores plan code, display name, request limits, bandwidth limits, concurrent connection limits, pricing, and feature flags. Seeded tiers are Free, Starter, Pro, and Enterprise.

Raw `usage_events` are append-only for auditability. `usage_rollups` stores billing-period aggregates for dashboard speed and invoice generation.

High-volume deployments should partition `usage_events` monthly, stream events to object storage, and aggregate rollups asynchronously from the worker queue.
