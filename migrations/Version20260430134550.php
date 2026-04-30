<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430134550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team_match (id SERIAL NOT NULL, team_id INT NOT NULL, match_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, home_team VARCHAR(255) NOT NULL, away_team VARCHAR(255) NOT NULL, home_score INT DEFAULT NULL, away_score INT DEFAULT NULL, venue VARCHAR(255) DEFAULT NULL, status VARCHAR(32) NOT NULL, match_day VARCHAR(64) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BD5D8C45296CD8AE ON team_match (team_id)');
        $this->addSql('CREATE UNIQUE INDEX match_unique ON team_match (team_id, match_date, home_team, away_team)');
        $this->addSql('COMMENT ON COLUMN team_match.match_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE team_match ADD CONSTRAINT FK_BD5D8C45296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team ADD championship_url VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE team_match DROP CONSTRAINT FK_BD5D8C45296CD8AE');
        $this->addSql('DROP TABLE team_match');
        $this->addSql('ALTER TABLE team DROP championship_url');
    }
}
