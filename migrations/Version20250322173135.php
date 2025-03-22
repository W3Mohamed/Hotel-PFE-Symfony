<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250322173135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, session_id VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE panier_chambres (id INT AUTO_INCREMENT NOT NULL, panier_id INT DEFAULT NULL, chambre_id INT DEFAULT NULL, nb_nuit INT NOT NULL, INDEX IDX_AC8C2010F77D927C (panier_id), INDEX IDX_AC8C20109B177F54 (chambre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE panier_service (id INT AUTO_INCREMENT NOT NULL, panier_chambre_id INT DEFAULT NULL, service_id_id INT DEFAULT NULL, INDEX IDX_1B275E53FFC246C1 (panier_chambre_id), INDEX IDX_1B275E53D63673B0 (service_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation_chambre (id INT AUTO_INCREMENT NOT NULL, reservation_id_id INT DEFAULT NULL, chambre_id_id INT DEFAULT NULL, nb_nuit INT NOT NULL, INDEX IDX_A29C5F7A3C3B4EF0 (reservation_id_id), INDEX IDX_A29C5F7A2680A339 (chambre_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, date_depart DATETIME NOT NULL, date_sortie DATETIME NOT NULL, status VARCHAR(30) NOT NULL, prix_total DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_4DA2399D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations_service (id INT AUTO_INCREMENT NOT NULL, reservation_chambre_id INT DEFAULT NULL, service_id_id INT DEFAULT NULL, INDEX IDX_6CA5E80CFBBDB6E0 (reservation_chambre_id), INDEX IDX_6CA5E80CD63673B0 (service_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE services (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, code_postale VARCHAR(255) NOT NULL, pays VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE panier_chambres ADD CONSTRAINT FK_AC8C2010F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('ALTER TABLE panier_chambres ADD CONSTRAINT FK_AC8C20109B177F54 FOREIGN KEY (chambre_id) REFERENCES chambres (id)');
        $this->addSql('ALTER TABLE panier_service ADD CONSTRAINT FK_1B275E53FFC246C1 FOREIGN KEY (panier_chambre_id) REFERENCES panier_chambres (id)');
        $this->addSql('ALTER TABLE panier_service ADD CONSTRAINT FK_1B275E53D63673B0 FOREIGN KEY (service_id_id) REFERENCES services (id)');
        $this->addSql('ALTER TABLE reservation_chambre ADD CONSTRAINT FK_A29C5F7A3C3B4EF0 FOREIGN KEY (reservation_id_id) REFERENCES reservations (id)');
        $this->addSql('ALTER TABLE reservation_chambre ADD CONSTRAINT FK_A29C5F7A2680A339 FOREIGN KEY (chambre_id_id) REFERENCES chambres (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA2399D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservations_service ADD CONSTRAINT FK_6CA5E80CFBBDB6E0 FOREIGN KEY (reservation_chambre_id) REFERENCES reservation_chambre (id)');
        $this->addSql('ALTER TABLE reservations_service ADD CONSTRAINT FK_6CA5E80CD63673B0 FOREIGN KEY (service_id_id) REFERENCES services (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier_chambres DROP FOREIGN KEY FK_AC8C2010F77D927C');
        $this->addSql('ALTER TABLE panier_chambres DROP FOREIGN KEY FK_AC8C20109B177F54');
        $this->addSql('ALTER TABLE panier_service DROP FOREIGN KEY FK_1B275E53FFC246C1');
        $this->addSql('ALTER TABLE panier_service DROP FOREIGN KEY FK_1B275E53D63673B0');
        $this->addSql('ALTER TABLE reservation_chambre DROP FOREIGN KEY FK_A29C5F7A3C3B4EF0');
        $this->addSql('ALTER TABLE reservation_chambre DROP FOREIGN KEY FK_A29C5F7A2680A339');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA2399D86650F');
        $this->addSql('ALTER TABLE reservations_service DROP FOREIGN KEY FK_6CA5E80CFBBDB6E0');
        $this->addSql('ALTER TABLE reservations_service DROP FOREIGN KEY FK_6CA5E80CD63673B0');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE panier_chambres');
        $this->addSql('DROP TABLE panier_service');
        $this->addSql('DROP TABLE reservation_chambre');
        $this->addSql('DROP TABLE reservations');
        $this->addSql('DROP TABLE reservations_service');
        $this->addSql('DROP TABLE services');
        $this->addSql('DROP TABLE user');
    }
}
