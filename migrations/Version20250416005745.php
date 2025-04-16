<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416005745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affecter_demande DROP INDEX UNIQ_B3C739DF80E95E18, ADD INDEX IDX_B3C739DF80E95E18 (demande_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affecter_demande DROP INDEX IDX_B3C739DF80E95E18, ADD UNIQUE INDEX UNIQ_B3C739DF80E95E18 (demande_id)');
    }
}
