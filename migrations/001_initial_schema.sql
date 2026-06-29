CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE plans (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), code TEXT NOT NULL UNIQUE, name TEXT NOT NULL UNIQUE, monthly_request_limit BIGINT, monthly_bandwidth_limit BIGINT, concurrent_connections INTEGER, price_cents INTEGER, features JSONB NOT NULL DEFAULT '[]'::jsonb);
CREATE TABLE users (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, plan_id UUID NOT NULL REFERENCES plans(id), is_active BOOLEAN NOT NULL DEFAULT TRUE, created_at TIMESTAMPTZ NOT NULL DEFAULT now());
CREATE TABLE subscriptions (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), stripe_customer_id TEXT, stripe_subscription_id TEXT, status TEXT NOT NULL, current_period_start TIMESTAMPTZ, current_period_end TIMESTAMPTZ);
CREATE TABLE api_keys (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), prefix TEXT NOT NULL UNIQUE, secret_hash TEXT NOT NULL, last_used_at TIMESTAMPTZ, created_at TIMESTAMPTZ NOT NULL DEFAULT now(), revoked_at TIMESTAMPTZ);
CREATE TABLE proxy_credentials (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), username TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, allowed_countries TEXT[] NOT NULL DEFAULT '{}', created_at TIMESTAMPTZ NOT NULL DEFAULT now(), revoked_at TIMESTAMPTZ);
CREATE TABLE countries (code CHAR(2) PRIMARY KEY, name TEXT NOT NULL, enabled BOOLEAN NOT NULL DEFAULT TRUE);
CREATE TABLE cities (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), country_code CHAR(2) NOT NULL REFERENCES countries(code), name TEXT NOT NULL, enabled BOOLEAN NOT NULL DEFAULT TRUE, UNIQUE(country_code, name));
CREATE TABLE vpn_nodes (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), country_code CHAR(2) NOT NULL REFERENCES countries(code), city_id UUID REFERENCES cities(id), name TEXT NOT NULL UNIQUE, provider TEXT NOT NULL, public_ip INET, healthy BOOLEAN NOT NULL DEFAULT FALSE, disabled_at TIMESTAMPTZ, last_health_check_at TIMESTAMPTZ, vpn_uptime_seconds BIGINT NOT NULL DEFAULT 0, last_latency_ms INTEGER);
CREATE TABLE proxy_nodes (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), vpn_node_id UUID NOT NULL REFERENCES vpn_nodes(id), name TEXT NOT NULL UNIQUE, host TEXT NOT NULL, port INTEGER NOT NULL, active_connections INTEGER NOT NULL DEFAULT 0, capacity INTEGER NOT NULL DEFAULT 500, weight INTEGER NOT NULL DEFAULT 100, healthy BOOLEAN NOT NULL DEFAULT FALSE);
CREATE TABLE usage_events (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), proxy_node_id UUID REFERENCES proxy_nodes(id), country_code CHAR(2), bytes_in BIGINT NOT NULL DEFAULT 0, bytes_out BIGINT NOT NULL DEFAULT 0, connection_seconds INTEGER NOT NULL DEFAULT 0, latency_ms INTEGER, status_code INTEGER, error_code TEXT, created_at TIMESTAMPTZ NOT NULL DEFAULT now());
CREATE TABLE usage_rollups (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), period TEXT NOT NULL, requests BIGINT NOT NULL DEFAULT 0, bytes_in BIGINT NOT NULL DEFAULT 0, bytes_out BIGINT NOT NULL DEFAULT 0, connection_seconds BIGINT NOT NULL DEFAULT 0, errors BIGINT NOT NULL DEFAULT 0, average_latency_ms INTEGER, countries JSONB NOT NULL DEFAULT '{}'::jsonb, UNIQUE(user_id, period));
CREATE TABLE sessions (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), proxy_node_id UUID NOT NULL REFERENCES proxy_nodes(id), sticky_key TEXT, started_at TIMESTAMPTZ NOT NULL DEFAULT now(), ended_at TIMESTAMPTZ);
CREATE TABLE invoices (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), user_id UUID NOT NULL REFERENCES users(id), stripe_invoice_id TEXT UNIQUE, amount_cents INTEGER NOT NULL, status TEXT NOT NULL, issued_at TIMESTAMPTZ NOT NULL DEFAULT now());
CREATE TABLE payments (id UUID PRIMARY KEY DEFAULT gen_random_uuid(), invoice_id UUID NOT NULL REFERENCES invoices(id), stripe_payment_intent_id TEXT UNIQUE, amount_cents INTEGER NOT NULL, status TEXT NOT NULL, paid_at TIMESTAMPTZ);

INSERT INTO plans (code, name, monthly_request_limit, monthly_bandwidth_limit, concurrent_connections, price_cents, features) VALUES
('free', 'Free', 10000, 1073741824, 5, 0, '["community_support"]'),
('starter', 'Starter', 250000, 53687091200, 50, 2900, '["email_support", "api_keys"]'),
('pro', 'Pro', 2000000, 536870912000, 500, 9900, '["priority_support", "sticky_sessions", "city_routing"]'),
('enterprise', 'Enterprise', NULL, NULL, NULL, NULL, '["dedicated_ips", "sso", "custom_slas", "account_manager"]');

CREATE INDEX usage_events_user_created_idx ON usage_events(user_id, created_at DESC);
CREATE INDEX usage_rollups_period_idx ON usage_rollups(period, user_id);
CREATE INDEX proxy_nodes_health_load_idx ON proxy_nodes(healthy, active_connections, weight DESC);
