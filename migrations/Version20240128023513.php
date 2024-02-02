<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240128023513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__configuration AS SELECT id, width, height, play, fps FROM configuration');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('CREATE TABLE configuration (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, is_paused BOOLEAN NOT NULL, fps INTEGER NOT NULL)');
        $this->addSql('INSERT INTO configuration (id, width, height, is_paused, fps) SELECT id, width, height, play, fps FROM __temp__configuration');
        $this->addSql('DROP TABLE __temp__configuration');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__configuration AS SELECT id, width, height, is_paused, fps FROM configuration');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('CREATE TABLE configuration (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, play BOOLEAN NOT NULL, fps INTEGER NOT NULL)');
        $this->addSql('INSERT INTO configuration (id, width, height, play, fps) SELECT id, width, height, is_paused, fps FROM __temp__configuration');
        $this->addSql('DROP TABLE __temp__configuration');
    }
}
