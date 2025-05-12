<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509095640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
    //     // this up() migration is auto-generated, please modify it to your needs
    //     $this->addSql('ALTER TABLE contrat CHANGE num_contrat num_contrat VARCHAR(10) NOT NULL');
    //     $this->addSql('CREATE UNIQUE INDEX UNIQ_60349993A537837A ON contrat (num_contrat)');
    // }
    }

    public function down(Schema $schema): void
    {
        // // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('DROP INDEX UNIQ_60349993A537837A ON contrat');
        // $this->addSql('ALTER TABLE contrat CHANGE num_contrat num_contrat INT NOT NULL');
    }
}
