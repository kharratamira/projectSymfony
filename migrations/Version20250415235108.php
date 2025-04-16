<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415235108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affecter_demande ADD statut VARCHAR(255) NOT NULL, CHANGE technicien_id technicien_id INT NOT NULL, CHANGE demande_id demande_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affecter_demande DROP statut, CHANGE technicien_id technicien_id INT DEFAULT NULL, CHANGE demande_id demande_id INT DEFAULT NULL');
    }
}
