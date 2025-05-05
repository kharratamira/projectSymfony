<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504143848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD demande_contrat_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455EEEF0CBE FOREIGN KEY (demande_contrat_id) REFERENCES demande_contrat (id)');
        $this->addSql('CREATE INDEX IDX_C7440455EEEF0CBE ON client (demande_contrat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455EEEF0CBE');
        $this->addSql('DROP INDEX IDX_C7440455EEEF0CBE ON client');
        $this->addSql('ALTER TABLE client DROP demande_contrat_id');
    }
}
