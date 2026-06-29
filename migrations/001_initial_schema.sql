CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE plans (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), name TEXT NOT NULL UNIQUE, monthly_request_limit BIGINT NOT NULL, monthly_bandwidth_limit BIGINT NOT NULL, price_cents INTEGER NOT NULL);
CREATE TABLE users (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, plan_id UUID NOT NULL REFERENCES plans(id), is_active BOOLEAN NOT NULL DEFAULT TRUE, created_at TIMESTAMPTZ NOT NULL DEFAULT now());
CREATE TABLE subscriptions (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), stripe_customer_id TEXT, stripe_subscription_id TEXT, status TEXT NOT NULL, current_period_start TIMESTAMPTZ, current_period_end TIMESTAMPTZ);
CREATE TABLE api_keys (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), prefix TEXT NOT NULL UNIQUE, secret_hash TEXT NOT NULL, last_used_at TIMESTAMPTZ, created_at TIMESTAMPTZ NOT NULL DEFAULT now(), revoked_at TIMESTAMPTZ);
CREATE TABLE proxy_credentials (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), username TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, allowed_countries TEXT[] NOT NULL DEFAULT '{}', created_at TIMESTAMPTZ NOT NULL DEFAULT now(), revoked_at TIMESTAMPTZ);
CREATE TABLE countries (code CHAR(2) PRIMARY KEY, name TEXT NOT NULL, enabled BOOLEAN NOT NULL DEFAULT TRUE);
CREATE TABLE cities (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), country_code CHAR(2) NOT NULL REFERENCES countries(code), name TEXT NOT NULL, enabled BOOLEAN NOT NULL DEFAULT TRUE, UNIQUE(country_code, name));
CREATE TABLE vpn_nodes (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), country_code CHAR(2) NOT NULL REFERENCES countries(code), city_id UUID REFERENCES cities(id), name TEXT NOT NULL UNIQUE, provider TEXT NOT NULL, public_ip INET, healthy BOOLEAN NOT NULL DEFAULT FALSE, disabled_at TIMESTAMPTZ, last_health_check_at TIMESTAMPTZ);
CREATE TABLE proxy_nodes (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), vpn_node_id UUID NOT NULL REFERENCES vpn_nodes(id), name TEXT NOT NULL UNIQUE, host TEXT NOT NULL, port INTEGER NOT NULL, active_connections INTEGER NOT NULL DEFAULT 0, weight INTEGER NOT NULL DEFAULT 100, healthy BOOLEAN NOT NULL DEFAULT FALSE);
CREATE TABLE usage_events (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), proxy_node_id UUID REFERENCES proxy_nodes(id), country_code CHAR(2), bytes_in BIGINT NOT NULL DEFAULT 0, bytes_out BIGINT NOT NULL DEFAULT 0, latency_ms INTEGER, status_code INTEGER, error_code TEXT, created_at TIMESTAMPTZ NOT NULL DEFAULT now());
CREATE TABLE sessions (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), proxy_node_id UUID NOT NULL REFERENCES proxy_nodes(id), sticky_key TEXT, started_at TIMESTAMPTZ NOT NULL DEFAULT now(), ended_at TIMESTAMPTZ);
CREATE TABLE invoices (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), stripe_invoice_id TEXT UNIQUE, amount_cents INTEGER NOT NULL, status TEXT NOT NULL, issued_at TIMESTAMPTZ NOT NULL DEFAULT now());
CREATE TABLE payments (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), invoice_id UUID NOT NULL REFERENCES invoices(id), stripe_payment_intent_id TEXT UNIQUE, amount_cents INTEGER NOT NULL, status TEXT NOT NULL, paid_at TIMESTAMPTZ);

CREATE INDEX usage_events_user_created_idx ON usage_events(user_id, created_at DESC);
CREATE INDEX proxy_nodes_health_load_idx ON proxy_nodes(healthy, active_connections, weight DESC);
