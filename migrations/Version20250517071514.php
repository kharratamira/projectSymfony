<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250517071514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE satisfaction_client (id INT AUTO_INCREMENT NOT NULL, intervention_id INT DEFAULT NULL, niveau VARCHAR(255) NOT NULL, commentaire LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_93B1FD528EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE satisfaction_client ADD CONSTRAINT FK_93B1FD528EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE satisfaction_client DROP FOREIGN KEY FK_93B1FD528EAE3863');
        $this->addSql('DROP TABLE satisfaction_client');
    }
}
