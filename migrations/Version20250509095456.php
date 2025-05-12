<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509095456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE contrat ADD num_contrat INT NOT NULL, DROP description_contrat');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE contrat ADD description_contrat VARCHAR(255) NOT NULL, DROP num_contrat');
    }
}
