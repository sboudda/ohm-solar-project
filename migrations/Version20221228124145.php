<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221228124145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE toit (id INT AUTO_INCREMENT NOT NULL, prospect_id INT DEFAULT NULL, status INT DEFAULT NULL, declinaison VARCHAR(100) DEFAULT NULL, orientation VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_65C50101D182060A (prospect_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE toit ADD CONSTRAINT FK_65C50101D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE toit DROP FOREIGN KEY FK_65C50101D182060A');
        $this->addSql('DROP TABLE toit');
    }
}
