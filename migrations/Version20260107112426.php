<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107112426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix AUTO_INCREMENT on personnel_doc.id and add useful indexes';
    }

    public function up(Schema $schema): void
    {
        // 1️⃣ Forcer l’ID technique en AUTO_INCREMENT (Doctrine + MySQL alignés)
        $this->addSql(
            'ALTER TABLE personnel_doc
             MODIFY id INT AUTO_INCREMENT NOT NULL'
        );

        // 2️⃣ flag_ligne conforme à l’entity
        $this->addSql(
            'ALTER TABLE personnel_doc
             MODIFY flag_ligne SMALLINT NOT NULL DEFAULT 0'
        );

        // 3️⃣ Index métier pour performances de synchronisation
        $this->addSql(
            'CREATE INDEX idx_personnel_doc_codagt ON personnel_doc (codagt)'
        );

        $this->addSql(
            'CREATE INDEX idx_personnel_doc_iddoc ON personnel_doc (IDDOC)'
        );
    }

    public function down(Schema $schema): void
    {
        // Rollback strict et sûr

        $this->addSql(
            'DROP INDEX idx_personnel_doc_codagt ON personnel_doc'
        );

        $this->addSql(
            'DROP INDEX idx_personnel_doc_iddoc ON personnel_doc'
        );

        $this->addSql(
            'ALTER TABLE personnel_doc
             MODIFY flag_ligne SMALLINT DEFAULT NULL'
        );

        // ⚠️ On ne retire PAS l’AUTO_INCREMENT en rollback
        // Cela évite de casser Doctrine si un rollback est fait en urgence
    }
}
