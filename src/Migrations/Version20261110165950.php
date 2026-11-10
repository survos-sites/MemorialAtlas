<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261110165950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add source and donor attribution to photographs and audio';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media ADD source VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD donor VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP source');
        $this->addSql('ALTER TABLE media DROP donor');
    }
}
