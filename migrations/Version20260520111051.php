<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520111051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, vip TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, numero VARCHAR(20) NOT NULL, statut VARCHAR(20) NOT NULL, total NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL, validated_at DATETIME DEFAULT NULL, client_id INT NOT NULL, UNIQUE INDEX UNIQ_6EEAA67DF55AE19E (numero), INDEX IDX_6EEAA67D19EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, numero VARCHAR(20) NOT NULL, fichier_pdf VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, commande_id INT NOT NULL, UNIQUE INDEX UNIQ_FE866410F55AE19E (numero), UNIQUE INDEX UNIQ_FE86641082EA2E54 (commande_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ligne_commande (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, commande_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_3170B74B82EA2E54 (commande_id), INDEX IDX_3170B74BF347EFB (produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(50) NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, stock INT NOT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_29A5EC27AEA34913 (reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641082EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641082EA2E54');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B82EA2E54');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BF347EFB');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
