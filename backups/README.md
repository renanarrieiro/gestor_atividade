# Backups Locais do Banco

Esta pasta guarda backups locais do banco MySQL.

## Script

Use o script `backup_mysql.ps1` para gerar dumps com timestamp:

```powershell
powershell -ExecutionPolicy Bypass -File .\backups\backup_mysql.ps1
```

## Exemplo com parâmetros

```powershell
powershell -ExecutionPolicy Bypass -File .\backups\backup_mysql.ps1 `
  -DbUser root `
  -DbName gestor_atividade `
  -DbHost 127.0.0.1 `
  -DbPort 3306
```

## Resultado

O arquivo será salvo nesta mesma pasta, por exemplo:

`gestor_atividade_2026-02-25_18-40-10.sql`

## Restauração

```powershell
mysql -u root -p gestor_atividade < .\backups\gestor_atividade_2026-02-25_18-40-10.sql
```

