<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261114142714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional cemetery GPS matching areas';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cemetery ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE cemetery ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE cemetery ADD matching_radius_meters INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cemetery DROP latitude');
        $this->addSql('ALTER TABLE cemetery DROP longitude');
        $this->addSql('ALTER TABLE cemetery DROP matching_radius_meters');
    }
}
