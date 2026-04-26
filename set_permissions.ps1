# Script PowerShell pour definir les permissions des dossiers (755) et fichiers (644)
# Usage: .\set_permissions.ps1

Write-Host "Definition des permissions des dossiers et fichiers..." -ForegroundColor Cyan
Write-Host "Dossiers: 755" -ForegroundColor Yellow
Write-Host "Fichiers: 644" -ForegroundColor Yellow
Write-Host ""

# Note: Sur Windows, les permissions Unix ne sont pas directement applicables
# Ce script utilise icacls pour definir des permissions equivalentes
# Pour les permissions Unix completes, utilisez le script bash sur un systeme Linux/WSL

$excludeDirs = @('node_modules', 'vendor', '.git', '.svn')

Write-Host "Mise a jour des permissions des dossiers..." -ForegroundColor Green
$dirs = Get-ChildItem -Path . -Recurse -Directory -Force -ErrorAction SilentlyContinue

foreach ($dir in $dirs) {
    $shouldExclude = $false
    foreach ($excludeDir in $excludeDirs) {
        if ($dir.FullName -like "*\$excludeDir\*" -or $dir.FullName -like "*\$excludeDir") {
            $shouldExclude = $true
            break
        }
    }

    if (-not $shouldExclude) {
        try {
            icacls $dir.FullName /grant "${env:USERNAME}:(OI)(CI)F" /T /Q 2>$null | Out-Null
        } catch {
            # Ignorer les erreurs
        }
    }
}

Write-Host "Mise a jour des permissions des fichiers..." -ForegroundColor Green
$files = Get-ChildItem -Path . -Recurse -File -Force -ErrorAction SilentlyContinue

foreach ($file in $files) {
    $shouldExclude = $false
    foreach ($excludeDir in $excludeDirs) {
        if ($file.FullName -like "*\$excludeDir\*" -or $file.FullName -like "*\$excludeDir") {
            $shouldExclude = $true
            break
        }
    }

    if (-not $shouldExclude) {
        try {
            icacls $file.FullName /grant "${env:USERNAME}:F" /Q 2>$null | Out-Null
        } catch {
            # Ignorer les erreurs
        }
    }
}

# Permissions speciales pour Laravel
Write-Host "Application des permissions speciales pour Laravel..." -ForegroundColor Green
if (Test-Path "storage") {
    icacls "storage" /grant "${env:USERNAME}:(OI)(CI)F" /T /Q 2>$null | Out-Null
    Write-Host "OK storage/ mis a jour" -ForegroundColor Green
}

if (Test-Path "bootstrap\cache") {
    icacls "bootstrap\cache" /grant "${env:USERNAME}:(OI)(CI)F" /T /Q 2>$null | Out-Null
    Write-Host "OK bootstrap\cache\ mis a jour" -ForegroundColor Green
}

if (Test-Path "artisan") {
    Write-Host "OK artisan trouve" -ForegroundColor Green
}

Write-Host ""
Write-Host "OK Permissions definies avec succes!" -ForegroundColor Green
Write-Host ""
Write-Host "Note: Sur Windows, les permissions Unix (755/644) ne sont pas directement applicables." -ForegroundColor Yellow
Write-Host "      Pour des permissions Unix completes, utilisez le script bash sur Linux/WSL." -ForegroundColor Yellow
