<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260629000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create identity, billing, proxy node, usage, and request logging tables with UUID primary keys and routing indexes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        $this->addSql('CREATE TABLE plans (id UUID NOT NULL, name VARCHAR(255) NOT NULL, stripe_price_id VARCHAR(255) DEFAULT NULL, monthly_quota_bytes INT NOT NULL, price_cents INT NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, plan_id UUID NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, roles JSON NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_users_email ON users (email)');
        $this->addSql("CREATE TABLE api_keys (id UUID NOT NULL, user_id UUID NOT NULL, name VARCHAR(255) NOT NULL, key_hash VARCHAR(255) NOT NULL, last_used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ip_whitelist JSON NOT NULL DEFAULT '[]', created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql('CREATE UNIQUE INDEX uniq_api_key_hash ON api_keys (key_hash)');
        $this->addSql('CREATE INDEX idx_api_key_active ON api_keys (user_id, revoked_at)');
        $this->addSql('CREATE TABLE subscriptions (id UUID NOT NULL, user_id UUID NOT NULL, plan_id UUID NOT NULL, status VARCHAR(255) NOT NULL, stripe_subscription_id VARCHAR(255) DEFAULT NULL, current_period_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, current_period_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE proxy_credentials (id UUID NOT NULL, user_id UUID NOT NULL, username VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_proxy_username ON proxy_credentials (username)');
        $this->addSql('CREATE TABLE countries (id UUID NOT NULL, iso_code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_country_iso ON countries (iso_code)');
        $this->addSql('CREATE TABLE cities (id UUID NOT NULL, country_id UUID NOT NULL, name VARCHAR(255) NOT NULL, region VARCHAR(255) DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_city_country ON cities (country_id, name)');
        $this->addSql('CREATE TABLE exit_nodes (id UUID NOT NULL, country_id UUID DEFAULT NULL, city_entity_id UUID DEFAULT NULL, hostname VARCHAR(255) NOT NULL, public_ip VARCHAR(255) DEFAULT NULL, vpn_container VARCHAR(255) NOT NULL, proxy_container VARCHAR(255) NOT NULL, healthy BOOLEAN NOT NULL, active_connections INT NOT NULL, weight INT NOT NULL, last_heartbeat_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_exit_node_health_load ON exit_nodes (healthy, active_connections)');
        $this->addSql('CREATE TABLE proxy_nodes (id UUID NOT NULL, exit_node_id UUID NOT NULL, hostname VARCHAR(255) NOT NULL, listen_host VARCHAR(255) NOT NULL, http_port INT NOT NULL, https_port INT NOT NULL, healthy BOOLEAN NOT NULL, active_connections INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE usages (id UUID NOT NULL, user_id UUID NOT NULL, api_key_id UUID DEFAULT NULL, plan_id UUID NOT NULL, period_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, period_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, bytes_in BIGINT NOT NULL, bytes_out BIGINT NOT NULL, request_count INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_usage_period ON usages (user_id, period_start, period_end)');
        $this->addSql('CREATE TABLE request_logs (id UUID NOT NULL, user_id UUID NOT NULL, api_key_id UUID DEFAULT NULL, exit_node_id UUID NOT NULL, proxy_node_id UUID DEFAULT NULL, protocol VARCHAR(255) NOT NULL, target_host VARCHAR(255) NOT NULL, target_port INT NOT NULL, bytes_in BIGINT NOT NULL, bytes_out BIGINT NOT NULL, status_code INT DEFAULT NULL, duration_ms INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_request_log_user_created ON request_logs (user_id, created_at)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_users_plan FOREIGN KEY (plan_id) REFERENCES plans (id)');
    }

    public function down(Schema $schema): void
    {
        foreach (['request_logs','usages','proxy_nodes','exit_nodes','cities','countries','proxy_credentials','subscriptions','api_keys','users','plans'] as $table) {
            $this->addSql('DROP TABLE IF EXISTS ' . $table . ' CASCADE');
        }
    }
}
