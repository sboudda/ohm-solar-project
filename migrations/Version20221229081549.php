<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221229081549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE toit DROP FOREIGN KEY FK_65C50101D182060A');
        $this->addSql('DROP INDEX UNIQ_65C50101D182060A ON toit');
        $this->addSql('ALTER TABLE toit CHANGE prospect_id prospect_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE toit ADD CONSTRAINT FK_65C501013E3A05BD FOREIGN KEY (prospect_id_id) REFERENCES prospect (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_65C501013E3A05BD ON toit (prospect_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE toit DROP FOREIGN KEY FK_65C501013E3A05BD');
        $this->addSql('DROP INDEX UNIQ_65C501013E3A05BD ON toit');
        $this->addSql('ALTER TABLE toit CHANGE prospect_id_id prospect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE toit ADD CONSTRAINT FK_65C50101D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_65C50101D182060A ON toit (prospect_id)');
    }
}
