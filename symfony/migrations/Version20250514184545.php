<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514184545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_simulation_order');
        $this->addSql('ALTER TABLE rugby_match RENAME COLUMN "order" TO step_number');
        $this->addSql('CREATE UNIQUE INDEX unique_simulation_stepNumber ON rugby_match (simulation_id, step_number)');
        $this->addSql('DROP INDEX unique_team_simulation_order');
        $this->addSql('DROP INDEX idx_simulation_order');
        $this->addSql('ALTER TABLE team_points RENAME COLUMN "order" TO step_number');
        $this->addSql('CREATE INDEX idx_simulation_stepNumber ON team_points (simulation_id, step_number)');
        $this->addSql('CREATE UNIQUE INDEX unique_team_simulation_stepNumber ON team_points (team_id, simulation_id, step_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX idx_simulation_stepNumber');
        $this->addSql('DROP INDEX unique_team_simulation_stepNumber');
        $this->addSql('ALTER TABLE team_points RENAME COLUMN step_number TO "order"');
        $this->addSql('CREATE UNIQUE INDEX unique_team_simulation_order ON team_points (team_id, simulation_id, "order")');
        $this->addSql('CREATE INDEX idx_simulation_order ON team_points (simulation_id, "order")');
        $this->addSql('DROP INDEX unique_simulation_stepNumber');
        $this->addSql('ALTER TABLE rugby_match RENAME COLUMN step_number TO "order"');
        $this->addSql('CREATE UNIQUE INDEX unique_simulation_order ON rugby_match (simulation_id, "order")');
    }
}
