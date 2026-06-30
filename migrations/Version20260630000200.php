<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add production checkout sessions and payment gateway audit tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE checkout_sessions (id UUID NOT NULL, user_id UUID NOT NULL, provider VARCHAR(32) NOT NULL, payment_method VARCHAR(32) NOT NULL, plan_code VARCHAR(64) NOT NULL, amount_cents INT NOT NULL, currency VARCHAR(3) NOT NULL, checkout_url TEXT NOT NULL, status VARCHAR(32) NOT NULL, provider_session_id VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_checkout_sessions_user_status ON checkout_sessions (user_id, status)');
        $this->addSql('CREATE INDEX idx_checkout_sessions_provider_session ON checkout_sessions (provider, provider_session_id)');
        $this->addSql('CREATE TABLE payment_gateway_events (id UUID NOT NULL, provider VARCHAR(32) NOT NULL, event_type VARCHAR(128) NOT NULL, provider_event_id VARCHAR(255) NOT NULL, payload JSON NOT NULL, received_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_payment_gateway_event ON payment_gateway_events (provider, provider_event_id)');
        $this->addSql('ALTER TABLE checkout_sessions ADD CONSTRAINT fk_checkout_sessions_user FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        foreach (['payment_gateway_events', 'checkout_sessions'] as $table) {
            $this->addSql('DROP TABLE IF EXISTS ' . $table . ' CASCADE');
        }
    }
}
