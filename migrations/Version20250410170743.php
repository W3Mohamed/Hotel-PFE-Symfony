<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410170743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_chambre DROP FOREIGN KEY FK_A29C5F7A2680A339');
        $this->addSql('ALTER TABLE reservation_chambre DROP FOREIGN KEY FK_A29C5F7A3C3B4EF0');
        $this->addSql('ALTER TABLE reservations_service DROP FOREIGN KEY FK_6CA5E80CD63673B0');
        $this->addSql('ALTER TABLE reservations_service DROP FOREIGN KEY FK_6CA5E80CFBBDB6E0');
        $this->addSql('DROP TABLE reservation_chambre');
        $this->addSql('DROP TABLE reservations_service');
        $this->addSql('ALTER TABLE panier ADD status TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA2399D86650F');
        $this->addSql('DROP INDEX UNIQ_4DA2399D86650F ON reservations');
        $this->addSql('ALTER TABLE reservations ADD user_id INT NOT NULL, ADD panier_id INT NOT NULL, ADD date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP user_id_id, DROP date_arrive, DROP date_depart');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4DA239A76ED395 ON reservations (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4DA239F77D927C ON reservations (panier_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_chambre (id INT AUTO_INCREMENT NOT NULL, reservation_id_id INT DEFAULT NULL, chambre_id_id INT DEFAULT NULL, nb_nuit INT NOT NULL, INDEX IDX_A29C5F7A3C3B4EF0 (reservation_id_id), INDEX IDX_A29C5F7A2680A339 (chambre_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reservations_service (id INT AUTO_INCREMENT NOT NULL, reservation_chambre_id INT DEFAULT NULL, service_id_id INT DEFAULT NULL, INDEX IDX_6CA5E80CFBBDB6E0 (reservation_chambre_id), INDEX IDX_6CA5E80CD63673B0 (service_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reservation_chambre ADD CONSTRAINT FK_A29C5F7A2680A339 FOREIGN KEY (chambre_id_id) REFERENCES chambres (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reservation_chambre ADD CONSTRAINT FK_A29C5F7A3C3B4EF0 FOREIGN KEY (reservation_id_id) REFERENCES reservations (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reservations_service ADD CONSTRAINT FK_6CA5E80CD63673B0 FOREIGN KEY (service_id_id) REFERENCES services (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reservations_service ADD CONSTRAINT FK_6CA5E80CFBBDB6E0 FOREIGN KEY (reservation_chambre_id) REFERENCES reservation_chambre (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE panier DROP status');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239A76ED395');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239F77D927C');
        $this->addSql('DROP INDEX UNIQ_4DA239A76ED395 ON reservations');
        $this->addSql('DROP INDEX UNIQ_4DA239F77D927C ON reservations');
        $this->addSql('ALTER TABLE reservations ADD user_id_id INT DEFAULT NULL, ADD date_arrive DATETIME NOT NULL, ADD date_depart DATETIME NOT NULL, DROP user_id, DROP panier_id, DROP date_creation');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA2399D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4DA2399D86650F ON reservations (user_id_id)');
    }
}
