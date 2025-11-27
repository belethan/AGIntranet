<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124153100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nomusu VARCHAR(120) NOT NULL, prenom VARCHAR(50) DEFAULT NULL, nompat VARCHAR(120) DEFAULT NULL, teleph VARCHAR(25) DEFAULT NULL, sexe INT NOT NULL, dtenai DATETIME DEFAULT NULL, comnai VARCHAR(100) NOT NULL, mail VARCHAR(255) DEFAULT NULL, telpro VARCHAR(25) DEFAULT NULL, mailpro VARCHAR(255) DEFAULT NULL, telport VARCHAR(25) DEFAULT NULL, notel VARCHAR(6) DEFAULT NULL, compteinfo VARCHAR(120) NOT NULL, site VARCHAR(100) DEFAULT NULL, service VARCHAR(100) DEFAULT NULL, codagtresp VARCHAR(7) DEFAULT NULL, serviceresp VARCHAR(100) DEFAULT NULL, nomresp VARCHAR(150) DEFAULT NULL, prenomresp VARCHAR(100) DEFAULT NULL, mailresp VARCHAR(255) DEFAULT NULL, nomcj VARCHAR(150) DEFAULT NULL, prenomcj VARCHAR(100) DEFAULT NULL, datenaicj DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', codnat VARCHAR(2) DEFAULT NULL, contacc VARCHAR(150) DEFAULT NULL, telacc VARCHAR(24) DEFAULT NULL, telportacc VARCHAR(24) DEFAULT NULL, libcom VARCHAR(100) DEFAULT NULL, codpos VARCHAR(8) DEFAULT NULL, codpay VARCHAR(3) DEFAULT NULL, nomrue VARCHAR(255) DEFAULT NULL, numrue VARCHAR(5) DEFAULT NULL, codagt VARCHAR(7) NOT NULL, roles VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
