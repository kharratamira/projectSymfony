<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415231014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention_technicien DROP FOREIGN KEY FK_D4D5564113457256');
        $this->addSql('ALTER TABLE intervention_technicien DROP FOREIGN KEY FK_D4D556418EAE3863');
        $this->addSql('DROP TABLE intervention_technicien');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB80E95E18');
        $this->addSql('DROP INDEX UNIQ_D11814AB80E95E18 ON intervention');
        $this->addSql('ALTER TABLE intervention DROP demande_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intervention_technicien (intervention_id INT NOT NULL, technicien_id INT NOT NULL, INDEX IDX_D4D556418EAE3863 (intervention_id), INDEX IDX_D4D5564113457256 (technicien_id), PRIMARY KEY(intervention_id, technicien_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE intervention_technicien ADD CONSTRAINT FK_D4D5564113457256 FOREIGN KEY (technicien_id) REFERENCES technicien (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_technicien ADD CONSTRAINT FK_D4D556418EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention ADD demande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB80E95E18 FOREIGN KEY (demande_id) REFERENCES demande_intervention (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D11814AB80E95E18 ON intervention (demande_id)');
    }
}
