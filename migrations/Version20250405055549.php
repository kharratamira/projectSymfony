<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405055549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE premission (id INT AUTO_INCREMENT NOT NULL, nom_premission VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE premission_role (premission_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_CCAC27E4194D9F1 (premission_id), INDEX IDX_CCAC27E4D60322AC (role_id), PRIMARY KEY(premission_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE premission_role ADD CONSTRAINT FK_CCAC27E4194D9F1 FOREIGN KEY (premission_id) REFERENCES premission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE premission_role ADD CONSTRAINT FK_CCAC27E4D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE premission_role DROP FOREIGN KEY FK_CCAC27E4194D9F1');
        $this->addSql('ALTER TABLE premission_role DROP FOREIGN KEY FK_CCAC27E4D60322AC');
        $this->addSql('DROP TABLE premission');
        $this->addSql('DROP TABLE premission_role');
    }
}
