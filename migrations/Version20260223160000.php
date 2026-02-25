<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Estrutura inicial do gestor de atividades';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE projeto (id INT AUTO_INCREMENT NOT NULL, nome VARCHAR(120) NOT NULL, criado_em DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE atividade (id INT AUTO_INCREMENT NOT NULL, projeto_id INT NOT NULL, id_externo VARCHAR(60) NOT NULL, descricao LONGTEXT NOT NULL, criado_em DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_3B8272F3166D1F9C (projeto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE sap_request (id INT AUTO_INCREMENT NOT NULL, atividade_id INT NOT NULL, tipo VARCHAR(30) NOT NULL, numero VARCHAR(30) NOT NULL, descricao LONGTEXT NOT NULL, modulo VARCHAR(30) NOT NULL, usuario VARCHAR(60) NOT NULL, status VARCHAR(40) NOT NULL, data_request DATE NOT NULL COMMENT '(DC2Type:date_immutable)', INDEX IDX_3EA17E149D8A9AA (atividade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE arquivo_atividade (id INT AUTO_INCREMENT NOT NULL, atividade_id INT NOT NULL, tipo VARCHAR(20) NOT NULL, nome_original VARCHAR(255) NOT NULL, caminho VARCHAR(255) NOT NULL, criado_em DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_630B1C2A149D8A9AA (atividade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE comentario_atividade (id INT AUTO_INCREMENT NOT NULL, atividade_id INT NOT NULL, texto LONGTEXT NOT NULL, criado_em DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_2A11B8EE149D8A9AA (atividade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql('ALTER TABLE atividade ADD CONSTRAINT FK_3B8272F3166D1F9C FOREIGN KEY (projeto_id) REFERENCES projeto (id)');
        $this->addSql('ALTER TABLE sap_request ADD CONSTRAINT FK_3EA17E149D8A9AA FOREIGN KEY (atividade_id) REFERENCES atividade (id)');
        $this->addSql('ALTER TABLE arquivo_atividade ADD CONSTRAINT FK_630B1C2A149D8A9AA FOREIGN KEY (atividade_id) REFERENCES atividade (id)');
        $this->addSql('ALTER TABLE comentario_atividade ADD CONSTRAINT FK_2A11B8EE149D8A9AA FOREIGN KEY (atividade_id) REFERENCES atividade (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comentario_atividade DROP FOREIGN KEY FK_2A11B8EE149D8A9AA');
        $this->addSql('ALTER TABLE arquivo_atividade DROP FOREIGN KEY FK_630B1C2A149D8A9AA');
        $this->addSql('ALTER TABLE sap_request DROP FOREIGN KEY FK_3EA17E149D8A9AA');
        $this->addSql('ALTER TABLE atividade DROP FOREIGN KEY FK_3B8272F3166D1F9C');

        $this->addSql('DROP TABLE comentario_atividade');
        $this->addSql('DROP TABLE arquivo_atividade');
        $this->addSql('DROP TABLE sap_request');
        $this->addSql('DROP TABLE atividade');
        $this->addSql('DROP TABLE projeto');
    }
}
