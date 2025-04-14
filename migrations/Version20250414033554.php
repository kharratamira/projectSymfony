<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414033554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_intervention CHANGE photo1 photo1 VARCHAR(255) DEFAULT NULL, CHANGE photo2 photo2 VARCHAR(255) DEFAULT NULL, CHANGE photo3 photo3 VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_intervention CHANGE photo1 photo1 VARCHAR(255) NOT NULL, CHANGE photo2 photo2 VARCHAR(255) NOT NULL, CHANGE photo3 photo3 VARCHAR(255) NOT NULL');
    }
}
