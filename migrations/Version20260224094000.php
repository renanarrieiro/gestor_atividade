<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224094000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Altera horas_trabalhadas para inteiro no relatorio de horas.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE registro_hora SET horas_trabalhadas = ROUND(horas_trabalhadas, 0)');
        $this->addSql('ALTER TABLE registro_hora MODIFY horas_trabalhadas INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registro_hora MODIFY horas_trabalhadas NUMERIC(5, 2) NOT NULL');
    }
}
