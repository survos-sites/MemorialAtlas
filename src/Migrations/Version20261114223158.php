<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261114223158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add item.dense_summary: one compact paragraph per record for the chat agent.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE item ADD dense_summary TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE item DROP dense_summary');
    }
}
