<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312204348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove Country table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE country');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE country (code CHAR(2) NOT NULL, PRIMARY KEY (code))');
    }
}
