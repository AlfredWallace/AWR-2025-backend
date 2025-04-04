<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327192429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team DROP country_name');
        $this->addSql('ALTER TABLE team ALTER external_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE team ALTER external_alt_id TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE team ADD country_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE team ALTER external_id TYPE INT');
        $this->addSql('ALTER TABLE team ALTER external_alt_id TYPE INT');
    }
}
