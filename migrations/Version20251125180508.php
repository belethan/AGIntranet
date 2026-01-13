<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251125180508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make user index drops safe and update user structure';
    }

    public function up(Schema $schema): void
    {
        // Sécurité : uniquement MySQL
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        // Suppression sécurisée de idxcodagt
        $idxCodagt = $this->connection->fetchOne(
            "SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'user'
               AND INDEX_NAME = 'idxcodagt'"
        );

        if ($idxCodagt !== false) {
            $this->addSql('DROP INDEX idxcodagt ON user');
        }

        // Suppression sécurisée de idxnomusu
        $idxNomusu = $this->connection->fetchOne(
            "SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'user'
               AND INDEX_NAME = 'idxnomusu'"
        );

        if ($idxNomusu !== false) {
            $this->addSql('DROP INDEX idxnomusu ON user');
        }

        // Modification de la table user
        $this->addSql(
            "ALTER TABLE user
                ADD telportpro VARCHAR(24) DEFAULT NULL,
                ADD compte_actif INT NOT NULL,
                ADD codagt_responsable VARCHAR(8) DEFAULT NULL,
                ADD nom_responsable VARCHAR(120) DEFAULT NULL,
                ADD prenom_responsable VARCHAR(100) DEFAULT NULL,
                ADD mail_responsable VARCHAR(150) DEFAULT NULL,
                ADD siteresp VARCHAR(100) DEFAULT NULL,
                DROP codagtresp,
                DROP mailresp,
                CHANGE nomresp compte_info VARCHAR(150) DEFAULT NULL,
                CHANGE prenomresp num_rpps VARCHAR(100) DEFAULT NULL"
        );
    }

    public function down(Schema $schema): void
    {
        // Sécurité : uniquement MySQL
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        // Restauration de la structure
        $this->addSql(
            "ALTER TABLE user
                ADD codagtresp VARCHAR(7) DEFAULT NULL,
                ADD nomresp VARCHAR(150) DEFAULT NULL,
                ADD prenomresp VARCHAR(100) DEFAULT NULL,
                ADD mailresp VARCHAR(255) DEFAULT NULL,
                DROP telportpro,
                DROP compte_info,
                DROP num_rpps,
                DROP compte_actif,
                DROP codagt_responsable,
                DROP nom_responsable,
                DROP prenom_responsable,
                DROP mail_responsable,
                DROP siteresp"
        );

        // Recréation sécurisée de idxcodagt
        $idxCodagt = $this->connection->fetchOne(
            "SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'user'
               AND INDEX_NAME = 'idxcodagt'"
        );

        if ($idxCodagt === false) {
            $this->addSql('CREATE UNIQUE INDEX idxcodagt ON user (codagt)');
        }

        // Recréation sécurisée de idxnomusu
        $idxNomusu = $this->connection->fetchOne(
            "SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'user'
               AND INDEX_NAME = 'idxnomusu'"
        );

        if ($idxNomusu === false) {
            $this->addSql('CREATE INDEX idxnomusu ON user (nomusu)');
        }
    }
}
