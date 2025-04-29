<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427211252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE autorisation_sortie ADD technicien_id INT NOT NULL');
        $this->addSql('ALTER TABLE autorisation_sortie ADD CONSTRAINT FK_AEA917E413457256 FOREIGN KEY (technicien_id) REFERENCES technicien (id)');
        $this->addSql('CREATE INDEX IDX_AEA917E413457256 ON autorisation_sortie (technicien_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE autorisation_sortie DROP FOREIGN KEY FK_AEA917E413457256');
        $this->addSql('DROP INDEX IDX_AEA917E413457256 ON autorisation_sortie');
        $this->addSql('ALTER TABLE autorisation_sortie DROP technicien_id');
    }
}
