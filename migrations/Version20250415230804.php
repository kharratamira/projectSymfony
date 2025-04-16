<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415230804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affecter_demande DROP FOREIGN KEY FK_B3C739DF8EAE3863');
        $this->addSql('DROP INDEX UNIQ_B3C739DF8EAE3863 ON affecter_demande');
        $this->addSql('ALTER TABLE affecter_demande DROP intervention_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affecter_demande ADD intervention_id INT NOT NULL');
        $this->addSql('ALTER TABLE affecter_demande ADD CONSTRAINT FK_B3C739DF8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3C739DF8EAE3863 ON affecter_demande (intervention_id)');
    }
}
