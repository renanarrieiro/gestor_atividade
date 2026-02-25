<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria configuracao mensal de taxa por hora para relatorio de horas.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE configuracao_hora_mensal (id INT AUTO_INCREMENT NOT NULL, ano SMALLINT NOT NULL, mes SMALLINT NOT NULL, taxa_hora NUMERIC(10, 2) NOT NULL, atualizado_em DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_configuracao_hora_mensal (ano, mes), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE configuracao_hora_mensal');
    }
}
