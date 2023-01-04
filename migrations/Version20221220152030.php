<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221220152030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, raw_address VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, town VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, geo_code LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_D4E6F8119EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agent (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, partner_id INT DEFAULT NULL, date_created DATETIME NOT NULL, status SMALLINT NOT NULL, UNIQUE INDEX UNIQ_268B9C9D9D86650F (user_id_id), INDEX IDX_268B9C9D9393F8FE (partner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, status SMALLINT DEFAULT NULL, date_created DATETIME DEFAULT NULL, level VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prospect (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, date_create DATETIME NOT NULL, status SMALLINT DEFAULT NULL, INDEX IDX_C9CE8C7D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, status SMALLINT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_terminated DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F8119EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE agent ADD CONSTRAINT FK_268B9C9D9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE agent ADD CONSTRAINT FK_268B9C9D9393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id)');
        $this->addSql('ALTER TABLE prospect ADD CONSTRAINT FK_C9CE8C7D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F8119EB6921');
        $this->addSql('ALTER TABLE agent DROP FOREIGN KEY FK_268B9C9D9D86650F');
        $this->addSql('ALTER TABLE agent DROP FOREIGN KEY FK_268B9C9D9393F8FE');
        $this->addSql('ALTER TABLE prospect DROP FOREIGN KEY FK_C9CE8C7D19EB6921');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE agent');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE prospect');
        $this->addSql('DROP TABLE user');
    }
}
