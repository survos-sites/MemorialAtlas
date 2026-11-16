<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The table of the "memorial_chat" message store (DoctrineDbalMessageStore::addTableToSchema()).
 * It is created here because ai:message-store:setup fails on a second run: the schema_filter in
 * doctrine.yaml hides chat_messages from introspection, so the store does not see the table.
 * Upstream: https://github.com/symfony/ai/issues/2960
 */
final class Version20261115180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create chat_messages for the chat agent message store.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS chat_messages (id VARCHAR(36) NOT NULL, version VARCHAR(36) NOT NULL, messages TEXT NOT NULL, updated_at INT NOT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE chat_messages');
    }
}
