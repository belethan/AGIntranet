<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125180508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idxcodagt ON user');
        $this->addSql('DROP INDEX idxnomusu ON user');
        $this->addSql('ALTER TABLE user ADD telportpro VARCHAR(24) DEFAULT NULL, ADD compte_actif INT NOT NULL, ADD codagt_responsable VARCHAR(8) DEFAULT NULL, ADD nom_responsable VARCHAR(120) DEFAULT NULL, ADD prenom_responsable VARCHAR(100) DEFAULT NULL, ADD mail_responsable VARCHAR(150) DEFAULT NULL, ADD siteresp VARCHAR(100) DEFAULT NULL, DROP codagtresp, DROP mailresp, CHANGE nomresp compte_info VARCHAR(150) DEFAULT NULL, CHANGE prenomresp num_rpps VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD codagtresp VARCHAR(7) DEFAULT NULL, ADD nomresp VARCHAR(150) DEFAULT NULL, ADD prenomresp VARCHAR(100) DEFAULT NULL, ADD mailresp VARCHAR(255) DEFAULT NULL, DROP telportpro, DROP compte_info, DROP num_rpps, DROP compte_actif, DROP codagt_responsable, DROP nom_responsable, DROP prenom_responsable, DROP mail_responsable, DROP siteresp');
        $this->addSql('CREATE UNIQUE INDEX idxcodagt ON user (codagt)');
        $this->addSql('CREATE INDEX idxnomusu ON user (nomusu)');
    }
}
