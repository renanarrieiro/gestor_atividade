<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria tabela de feriados personalizados para simulacao de faturamento no relatorio de horas.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE feriado (id INT AUTO_INCREMENT NOT NULL, data DATE NOT NULL, descricao VARCHAR(120) DEFAULT NULL, criado_em DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_feriado_data (data), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE feriado');
    }
}
