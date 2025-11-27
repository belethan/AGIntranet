<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126094705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE personnel_doc (id INT AUTO_INCREMENT NOT NULL, codagt VARCHAR(8) NOT NULL, iddoc INT NOT NULL, doc_ref VARCHAR(100) DEFAULT NULL, dtedeb DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', flag_actif SMALLINT NOT NULL, dtefin DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', dtecreation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', dtemodif DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', opecreation VARCHAR(30) DEFAULT NULL, opemodif VARCHAR(30) DEFAULT NULL, libtype VARCHAR(100) DEFAULT NULL, flag_ligne SMALLINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE personnel_doc');
    }
}
