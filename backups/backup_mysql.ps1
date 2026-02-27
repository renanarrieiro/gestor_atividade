param(
    [string]$DbUser = "root",
    [string]$DbName = "gestor_atividade",
    [string]$DbHost = "127.0.0.1",
    [int]$DbPort = 3306
)

$ErrorActionPreference = "Stop"

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$outputFile = Join-Path $scriptDir ("{0}_{1}.sql" -f $DbName, $timestamp)

Write-Host "Gerando backup de '$DbName' em '$outputFile'..."

# -p sem senha inline evita expor credencial em histórico.
$args = @(
    "-h", $DbHost,
    "-P", $DbPort,
    "-u", $DbUser,
    "-p",
    "--routines",
    "--triggers",
    "--events",
    "--single-transaction",
    "--default-character-set=utf8mb4",
    $DbName
)

$dump = Start-Process -FilePath "mysqldump" -ArgumentList $args -NoNewWindow -RedirectStandardOutput $outputFile -PassThru -Wait

if ($dump.ExitCode -ne 0) {
    throw "Falha ao executar mysqldump (ExitCode: $($dump.ExitCode))."
}

Write-Host "Backup concluído com sucesso."
Write-Host "Arquivo: $outputFile"
