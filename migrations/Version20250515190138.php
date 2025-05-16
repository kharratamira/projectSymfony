<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515190138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mode_paiement_facture (mode_paiement_id INT NOT NULL, facture_id INT NOT NULL, INDEX IDX_DB20CD4C438F5B63 (mode_paiement_id), INDEX IDX_DB20CD4C7F2DEE08 (facture_id), PRIMARY KEY(mode_paiement_id, facture_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mode_paiement_facture ADD CONSTRAINT FK_DB20CD4C438F5B63 FOREIGN KEY (mode_paiement_id) REFERENCES mode_paiement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mode_paiement_facture ADD CONSTRAINT FK_DB20CD4C7F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mode_paiement_mode_paiement DROP FOREIGN KEY FK_E246F93C96F183C1');
        $this->addSql('ALTER TABLE mode_paiement_mode_paiement DROP FOREIGN KEY FK_E246F93C8F14D34E');
        $this->addSql('DROP TABLE mode_paiement_mode_paiement');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mode_paiement_mode_paiement (mode_paiement_source INT NOT NULL, mode_paiement_target INT NOT NULL, INDEX IDX_E246F93C96F183C1 (mode_paiement_source), INDEX IDX_E246F93C8F14D34E (mode_paiement_target), PRIMARY KEY(mode_paiement_source, mode_paiement_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE mode_paiement_mode_paiement ADD CONSTRAINT FK_E246F93C96F183C1 FOREIGN KEY (mode_paiement_source) REFERENCES mode_paiement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mode_paiement_mode_paiement ADD CONSTRAINT FK_E246F93C8F14D34E FOREIGN KEY (mode_paiement_target) REFERENCES mode_paiement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mode_paiement_facture DROP FOREIGN KEY FK_DB20CD4C438F5B63');
        $this->addSql('ALTER TABLE mode_paiement_facture DROP FOREIGN KEY FK_DB20CD4C7F2DEE08');
        $this->addSql('DROP TABLE mode_paiement_facture');
    }
}
