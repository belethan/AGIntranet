<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107090640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique index on user.codagt (clé métier AGDUC)';
    }

    public function up(Schema $schema): void
    {
        // Clé métier AGDUC : codagt doit être unique
        $this->addSql('CREATE UNIQUE INDEX uniq_user_codagt ON user (codagt)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_user_codagt ON user');
    }
}
