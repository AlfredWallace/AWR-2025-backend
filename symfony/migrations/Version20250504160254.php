<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504160254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rugby_match (id SERIAL NOT NULL, home_team_id INT NOT NULL, away_team_id INT NOT NULL, simulation_id VARCHAR(255) NOT NULL, home_score INT NOT NULL, away_score INT NOT NULL, is_world_cup BOOLEAN NOT NULL, "order" INT NOT NULL, points_exchanged DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4F34426B9C4C13F6 ON rugby_match (home_team_id)');
        $this->addSql('CREATE INDEX IDX_4F34426B45185D02 ON rugby_match (away_team_id)');
        $this->addSql('CREATE INDEX IDX_4F34426BFEC09103 ON rugby_match (simulation_id)');
        $this->addSql('CREATE TABLE simulation (id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) DEFAULT NULL, max_steps INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN simulation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE team_points (id SERIAL NOT NULL, team_id INT NOT NULL, simulation_id VARCHAR(255) NOT NULL, "order" INT NOT NULL, points DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_51A1C9F0296CD8AE ON team_points (team_id)');
        $this->addSql('CREATE INDEX IDX_51A1C9F0FEC09103 ON team_points (simulation_id)');
        $this->addSql('CREATE INDEX idx_simulation_order ON team_points (simulation_id, "order")');
        $this->addSql('CREATE UNIQUE INDEX unique_team_simulation_order ON team_points (team_id, simulation_id, "order")');
        $this->addSql('ALTER TABLE rugby_match ADD CONSTRAINT FK_4F34426B9C4C13F6 FOREIGN KEY (home_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rugby_match ADD CONSTRAINT FK_4F34426B45185D02 FOREIGN KEY (away_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rugby_match ADD CONSTRAINT FK_4F34426BFEC09103 FOREIGN KEY (simulation_id) REFERENCES simulation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_points ADD CONSTRAINT FK_51A1C9F0296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_points ADD CONSTRAINT FK_51A1C9F0FEC09103 FOREIGN KEY (simulation_id) REFERENCES simulation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE rugby_match DROP CONSTRAINT FK_4F34426B9C4C13F6');
        $this->addSql('ALTER TABLE rugby_match DROP CONSTRAINT FK_4F34426B45185D02');
        $this->addSql('ALTER TABLE rugby_match DROP CONSTRAINT FK_4F34426BFEC09103');
        $this->addSql('ALTER TABLE team_points DROP CONSTRAINT FK_51A1C9F0296CD8AE');
        $this->addSql('ALTER TABLE team_points DROP CONSTRAINT FK_51A1C9F0FEC09103');
        $this->addSql('DROP TABLE rugby_match');
        $this->addSql('DROP TABLE simulation');
        $this->addSql('DROP TABLE team_points');
    }
}
