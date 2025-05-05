<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504143644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_contrat (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, date_demande DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE demannde_contrat');
        $this->addSql('ALTER TABLE contrat ADD demande_contrat_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_60349993EEEF0CBE FOREIGN KEY (demande_contrat_id) REFERENCES demande_contrat (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_60349993EEEF0CBE ON contrat (demande_contrat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_60349993EEEF0CBE');
        $this->addSql('CREATE TABLE demannde_contrat (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_demande DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE demande_contrat');
        $this->addSql('DROP INDEX UNIQ_60349993EEEF0CBE ON contrat');
        $this->addSql('ALTER TABLE contrat DROP demande_contrat_id');
    }
}
