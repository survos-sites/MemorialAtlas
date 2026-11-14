<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261114222452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add item.tags: symbols seen on the stone, for filtering.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE item ADD tags JSONB DEFAULT \'[]\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE item DROP tags');
    }
}
