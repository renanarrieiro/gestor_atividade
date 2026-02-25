# Gestor de Atividades SAP ABAP

Sistema web em PHP + Symfony + MySQL para gerenciamento de projetos, atividades ABAP, SAP Requests, anexos (especificação técnica/funcional) e comentários de status.

## Requisitos

- PHP 8.2+
- Composer 2+
- MySQL 8+

## Configuração

1. Ajuste o `DATABASE_URL` no arquivo `.env` se necessário.
2. Instale dependências:

```bash
composer install
```

3. Execute as migrations:

```bash
php bin/console doctrine:migrations:migrate
```

4. Suba o servidor local:

```bash
symfony serve
```

Ou, sem Symfony CLI:

```bash
php -S 127.0.0.1:8000 -t public
```

## Funcionalidades

- Cadastro de projetos
- Cadastro de atividades por projeto
- Inclusão de múltiplas SAP Requests por atividade
- Upload de arquivos por atividade em duas seções:
  - Especificação Técnica
  - Especificação Funcional
- Comentários de situação atual da atividade
- Página global de Requests com filtros:
  - Projeto
  - Atividade
  - Tipo
  - Módulo
  - Status
  - Número
- Relatório de horas trabalhadas:
  - Registro diário com data, horas e comentários
  - Filtro por mes e ano
  - Exportacao para `.xlsx` com totalizador de horas

## Estrutura principal

- Entidades: `src/Entity`
- Repositórios: `src/Repository`
- Controladores: `src/Controller`
- Formulários: `src/Form`
- Views Twig: `templates`
- Migração inicial: `migrations/Version20260223160000.php`

## Uploads

Arquivos anexados são salvos em:

- `var/uploads/tecnica`
- `var/uploads/funcional`
