<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240218213140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorite (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, article_id INT NOT NULL, reader_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68C58ED97294869C ON favorite (article_id)');
        $this->addSql('CREATE INDEX IDX_68C58ED91717D737 ON favorite (reader_id)');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED97294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED91717D737 FOREIGN KEY (reader_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE favorite DROP CONSTRAINT FK_68C58ED97294869C');
        $this->addSql('ALTER TABLE favorite DROP CONSTRAINT FK_68C58ED91717D737');
        $this->addSql('DROP TABLE favorite');
    }
}
