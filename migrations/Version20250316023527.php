<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250316023527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       // $this->addSql('ALTER TABLE client DROP nom_societe');
        // $this->addSql('ALTER TABLE compte_client ADD relation_id INT DEFAULT NULL');
        // $this->addSql('ALTER TABLE compte_client ADD CONSTRAINT FK_1DDD5D623256915B FOREIGN KEY (relation_id) REFERENCES client (id)');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_1DDD5D623256915B ON compte_client (relation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        //$this->addSql('ALTER TABLE client ADD nom_societe VARCHAR(255) NOT NULL');
        // $this->addSql('ALTER TABLE compte_client DROP FOREIGN KEY FK_1DDD5D623256915B');
        // $this->addSql('DROP INDEX UNIQ_1DDD5D623256915B ON compte_client');
        // $this->addSql('ALTER TABLE compte_client DROP relation_id');
    }
}
