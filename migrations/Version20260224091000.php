<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224091000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria tabela de registros diarios de horas para relatorio mensal.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE registro_hora (id INT AUTO_INCREMENT NOT NULL, data DATE NOT NULL COMMENT '(DC2Type:date_immutable)', horas_trabalhadas NUMERIC(5, 2) NOT NULL, comentarios LONGTEXT NOT NULL, criado_em DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX uniq_registro_hora_data (data), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE registro_hora');
    }
}
