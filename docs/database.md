# Database Schema

The initial PostgreSQL schema is in `migrations/001_initial_schema.sql` and includes users, plans, subscriptions, API keys, proxy credentials, countries, cities, VPN nodes, proxy nodes, usage events, sessions, invoices, and payments.

Usage events are append-only and indexed by user and timestamp. High-volume deployments should partition `usage_events` monthly and aggregate into rollup tables for billing reports.
