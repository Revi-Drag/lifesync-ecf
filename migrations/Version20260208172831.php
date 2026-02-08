<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208172831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task ADD done_at DATETIME DEFAULT NULL, ADD done_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2535AE3EF9 FOREIGN KEY (done_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_527EDB2535AE3EF9 ON task (done_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2535AE3EF9');
        $this->addSql('DROP INDEX IDX_527EDB2535AE3EF9 ON task');
        $this->addSql('ALTER TABLE task DROP done_at, DROP done_by_id');
    }
}
