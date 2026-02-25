<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223201000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Evita atividade duplicada por projeto e limpa duplicidades existentes';
    }

    public function up(Schema $schema): void
    {
        // Mantém o menor id (mais antigo) para cada combinação projeto + id_externo
        $this->addSql('DELETE a1 FROM atividade a1 INNER JOIN atividade a2 ON a1.projeto_id = a2.projeto_id AND a1.id_externo = a2.id_externo AND a1.id > a2.id');
        $this->addSql('CREATE UNIQUE INDEX uniq_atividade_projeto_idexterno ON atividade (projeto_id, id_externo)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_atividade_projeto_idexterno ON atividade');
    }
}
