<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250322020937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
{
    // First, ensure there is a valid relationship between user and client
    // Optionally, update or clean the data as needed, or remove the foreign key temporarily

    // $this->addSql('ALTER TABLE user ADD user_type VARCHAR(255) NOT NULL');  // Add the user_type column
    
    // // Ensure the client table has no existing foreign key constraints before adding the new one
    // $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455BF396750');
    // $this->addSql('ALTER TABLE client DROP email, CHANGE id id INT NOT NULL');  // Ensure no conflicts

    // // Add the foreign key constraint again after cleaning the data
    // $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
}


public function down(Schema $schema): void
{
    // Replace with the correct foreign key name
    // $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_1234567890ABCDEF');
    
    // // Modify the client table, restore the email column and change the id column to AUTO_INCREMENT
    // $this->addSql('ALTER TABLE client ADD email VARCHAR(255) NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
    
    // // Drop the user_type column from the user table
    // $this->addSql('ALTER TABLE user DROP user_type');
}

}
