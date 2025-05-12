<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509181412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE contrat CHANGE num_contrat num_contrat VARCHAR(10) NOT NULL, ADD PRIMARY KEY (id)');
        // $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_60349993EEEF0CBE FOREIGN KEY (demande_contrat_id) REFERENCES demande_contrat (id)');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_60349993A537837A ON contrat (num_contrat)');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_60349993EEEF0CBE ON contrat (demande_contrat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE contrat MODIFY id INT NOT NULL');
        // $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_60349993EEEF0CBE');
        // $this->addSql('DROP INDEX UNIQ_60349993A537837A ON contrat');
        // $this->addSql('DROP INDEX UNIQ_60349993EEEF0CBE ON contrat');
        // $this->addSql('DROP INDEX `primary` ON contrat');
        // $this->addSql('ALTER TABLE contrat CHANGE num_contrat num_contrat VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
