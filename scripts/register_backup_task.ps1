param(
    [string]$TaskName = "QuestBackupDaily",
    [string]$ScheduleTime = "02:00"
)

$ErrorActionPreference = "Stop"

$defaultPhpPath = "C:\xampp\php\php.exe"
$phpPath = if (Test-Path $defaultPhpPath) { $defaultPhpPath } else { (Get-Command php.exe).Source }
$scriptPath = Join-Path $PSScriptRoot "run_backup.php"
$taskAction = "`"$phpPath`" `"$scriptPath`""

schtasks /Create /F /SC DAILY /ST $ScheduleTime /TN $TaskName /TR $taskAction | Out-Null

Write-Host "Tarefa registrada com sucesso: $TaskName às $ScheduleTime"
