<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250323074312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Supprimer les colonnes inutiles
       // $this->addSql('ALTER TABLE client DROP nom, DROP prenom, DROP numero_telephone');
    
        // Ajouter les nouvelles colonnes avec gestion correcte du champ date_creation
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(255) NOT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD num_tel INT NOT NULL');
    
        // Étape 1 : ajouter la colonne NULL temporairement
        $this->addSql('ALTER TABLE user ADD date_creation DATE DEFAULT NULL');
    
        // Étape 2 : mettre à jour les données existantes
        $this->addSql('UPDATE user SET date_creation = CURRENT_DATE WHERE date_creation IS NULL');
    
        // Étape 3 : appliquer la contrainte NOT NULL
        $this->addSql('ALTER TABLE user MODIFY date_creation DATE NOT NULL');
    }
    

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD nom VARCHAR(255) NOT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD numero_telephone INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP nom, DROP prenom, DROP num_tel, DROP date_creation');
    }
}
