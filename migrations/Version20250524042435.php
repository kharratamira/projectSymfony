<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250524042435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE satisfaction_client DROP INDEX IDX_93B1FD528EAE3863, ADD UNIQUE INDEX UNIQ_93B1FD528EAE3863 (intervention_id)');
        // $this->addSql('ALTER TABLE satisfaction_client CHANGE intervention_id intervention_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE satisfaction_client DROP INDEX UNIQ_93B1FD528EAE3863, ADD INDEX IDX_93B1FD528EAE3863 (intervention_id)');
        // $this->addSql('ALTER TABLE satisfaction_client CHANGE intervention_id intervention_id INT DEFAULT NULL');
    }
}
