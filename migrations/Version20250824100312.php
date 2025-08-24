<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250824100312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, assigned_to_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(20) DEFAULT NULL, mobile VARCHAR(20) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, source VARCHAR(50) DEFAULT NULL, annual_revenue NUMERIC(10, 2) DEFAULT NULL, employee_count INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, last_contact_at DATETIME DEFAULT NULL, next_follow_up_at DATETIME DEFAULT NULL, is_active TINYINT(1) NOT NULL, website VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, tags LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_81398E09E7927C74 (email), INDEX IDX_81398E09F4BD7827 (assigned_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09F4BD7827');
        $this->addSql('DROP TABLE customer');
    }
}
