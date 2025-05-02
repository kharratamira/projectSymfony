<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501124100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention ADD affectation_id INT DEFAULT NULL, DROP description, DROP date_prevu_intervention, DROP date_reele_intervention');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB6D0ABA22 FOREIGN KEY (affectation_id) REFERENCES affecter_demande (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D11814AB6D0ABA22 ON intervention (affectation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB6D0ABA22');
        $this->addSql('DROP INDEX UNIQ_D11814AB6D0ABA22 ON intervention');
        $this->addSql('ALTER TABLE intervention ADD description VARCHAR(255) NOT NULL, ADD date_prevu_intervention DATE NOT NULL, ADD date_reele_intervention DATE NOT NULL, DROP affectation_id');
    }
}
