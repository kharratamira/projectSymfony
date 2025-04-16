<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415230527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE affecter_demande (id INT AUTO_INCREMENT NOT NULL, technicien_id INT DEFAULT NULL, demande_id INT DEFAULT NULL, intervention_id INT NOT NULL, date_prevu DATETIME NOT NULL, INDEX IDX_B3C739DF13457256 (technicien_id), UNIQUE INDEX UNIQ_B3C739DF80E95E18 (demande_id), UNIQUE INDEX UNIQ_B3C739DF8EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE affecter_demande ADD CONSTRAINT FK_B3C739DF13457256 FOREIGN KEY (technicien_id) REFERENCES technicien (id)');
        $this->addSql('ALTER TABLE affecter_demande ADD CONSTRAINT FK_B3C739DF80E95E18 FOREIGN KEY (demande_id) REFERENCES demande_intervention (id)');
        $this->addSql('ALTER TABLE affecter_demande ADD CONSTRAINT FK_B3C739DF8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affecter_demande DROP FOREIGN KEY FK_B3C739DF13457256');
        $this->addSql('ALTER TABLE affecter_demande DROP FOREIGN KEY FK_B3C739DF80E95E18');
        $this->addSql('ALTER TABLE affecter_demande DROP FOREIGN KEY FK_B3C739DF8EAE3863');
        $this->addSql('DROP TABLE affecter_demande');
    }
}
