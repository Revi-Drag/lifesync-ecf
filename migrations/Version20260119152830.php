<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119152830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, difficulty SMALLINT NOT NULL, duration_minutes INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, no VARCHAR(255) NOT NULL, created_by_id INT NOT NULL, assigned_to_id INT DEFAULT NULL, INDEX IDX_527EDB25B03A8386 (created_by_id), INDEX IDX_527EDB25F4BD7827 (assigned_to_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25B03A8386');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25F4BD7827');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE user');
    }
}
