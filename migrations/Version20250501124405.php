<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501124405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tache_intervention (tache_id INT NOT NULL, intervention_id INT NOT NULL, INDEX IDX_40EF719BD2235D39 (tache_id), INDEX IDX_40EF719B8EAE3863 (intervention_id), PRIMARY KEY(tache_id, intervention_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tache_intervention ADD CONSTRAINT FK_40EF719BD2235D39 FOREIGN KEY (tache_id) REFERENCES tache (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tache_intervention ADD CONSTRAINT FK_40EF719B8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tache_intervention DROP FOREIGN KEY FK_40EF719BD2235D39');
        $this->addSql('ALTER TABLE tache_intervention DROP FOREIGN KEY FK_40EF719B8EAE3863');
        $this->addSql('DROP TABLE tache_intervention');
    }
}
