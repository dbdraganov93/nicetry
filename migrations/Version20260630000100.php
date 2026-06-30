<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add scalable routing policies, exit pools, target policies, and scrape jobs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE routing_policies (id UUID NOT NULL, user_id UUID NOT NULL, allowed_countries JSON NOT NULL, allowed_targets JSON NOT NULL, max_concurrent_requests INT NOT NULL, sticky_sessions_enabled BOOLEAN NOT NULL, dedicated_pool_id VARCHAR(255) DEFAULT NULL, priority INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_routing_policy_user ON routing_policies (user_id)');
        $this->addSql('CREATE INDEX idx_routing_policy_priority ON routing_policies (priority)');
        $this->addSql('CREATE TABLE exit_pools (id VARCHAR(255) NOT NULL, country_code VARCHAR(2) NOT NULL, pool_type VARCHAR(32) NOT NULL, client_id UUID DEFAULT NULL, desired_nodes INT NOT NULL, healthy_nodes INT NOT NULL, queue_depth INT NOT NULL, active_connections INT NOT NULL, scale_signal VARCHAR(64) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_exit_pool_country_type ON exit_pools (country_code, pool_type)');
        $this->addSql('CREATE TABLE target_policies (id UUID NOT NULL, domain VARCHAR(255) NOT NULL, risk_level VARCHAR(64) NOT NULL, min_delay_ms INT NOT NULL, max_rps_per_client INT NOT NULL, requires_js BOOLEAN NOT NULL, requires_sticky_session BOOLEAN NOT NULL, allowed_countries JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_target_policy_domain ON target_policies (domain)');
        $this->addSql('CREATE TABLE scrape_jobs (id UUID NOT NULL, user_id UUID NOT NULL, url TEXT NOT NULL, country_code VARCHAR(2) NOT NULL, target_host VARCHAR(255) NOT NULL, priority INT NOT NULL, status VARCHAR(32) NOT NULL, attempts INT NOT NULL, queue_name VARCHAR(255) NOT NULL, result_location TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_scrape_job_queue_status ON scrape_jobs (queue_name, status, priority)');
        $this->addSql('ALTER TABLE routing_policies ADD CONSTRAINT fk_routing_policy_user FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE scrape_jobs ADD CONSTRAINT fk_scrape_job_user FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        foreach (['scrape_jobs', 'target_policies', 'exit_pools', 'routing_policies'] as $table) {
            $this->addSql('DROP TABLE IF EXISTS ' . $table . ' CASCADE');
        }
    }
}
