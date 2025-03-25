<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319211227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE client ADD entreprise VARCHAR(255) NOT NULL');
        // $this->addSql('ALTER TABLE compte_client DROP FOREIGN KEY FK_1DDD5D623256915B');
        // $this->addSql('ALTER TABLE compte_client CHANGE client_id client_id INT NOT NULL');
        // $this->addSql('DROP INDEX uniq_1ddd5d623256915b ON compte_client');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_1DDD5D6219EB6921 ON compte_client (client_id)');
        // $this->addSql('ALTER TABLE compte_client ADD CONSTRAINT FK_1DDD5D623256915B FOREIGN KEY (client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE client DROP entreprise');
        // $this->addSql('ALTER TABLE compte_client DROP FOREIGN KEY FK_1DDD5D6219EB6921');
        // $this->addSql('ALTER TABLE compte_client CHANGE client_id client_id INT DEFAULT NULL');
        // $this->addSql('DROP INDEX uniq_1ddd5d6219eb6921 ON compte_client');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_1DDD5D623256915B ON compte_client (client_id)');
        // $this->addSql('ALTER TABLE compte_client ADD CONSTRAINT FK_1DDD5D6219EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
    }
}
