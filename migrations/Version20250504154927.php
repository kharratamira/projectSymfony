<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504154927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455EEEF0CBE');
        $this->addSql('DROP INDEX IDX_C7440455EEEF0CBE ON client');
        $this->addSql('ALTER TABLE client DROP demande_contrat_id');
        $this->addSql('ALTER TABLE demande_contrat ADD client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_contrat ADD CONSTRAINT FK_50C3223C19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_50C3223C19EB6921 ON demande_contrat (client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD demande_contrat_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455EEEF0CBE FOREIGN KEY (demande_contrat_id) REFERENCES demande_contrat (id)');
        $this->addSql('CREATE INDEX IDX_C7440455EEEF0CBE ON client (demande_contrat_id)');
        $this->addSql('ALTER TABLE demande_contrat DROP FOREIGN KEY FK_50C3223C19EB6921');
        $this->addSql('DROP INDEX IDX_50C3223C19EB6921 ON demande_contrat');
        $this->addSql('ALTER TABLE demande_contrat DROP client_id');
    }
}
