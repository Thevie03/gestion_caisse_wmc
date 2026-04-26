# Script PowerShell pour vérifier les permissions Windows
# Usage: .\check_permissions_windows.ps1

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  VÉRIFICATION DES PERMISSIONS WINDOWS" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

$errors = @()
$warnings = @()
$checked = @{
    Directories = 0
    Files = 0
}

$excludeDirs = @('node_modules', 'vendor', '.git', '.svn', 'storage\framework\cache', 'storage\framework\sessions', 'storage\framework\views', 'storage\logs', 'bootstrap\cache')

function Test-PathExcluded {
    param([string]$path)

    foreach ($exclude in $excludeDirs) {
        if ($path -like "*\$exclude\*" -or $path -like "*\$exclude") {
            return $true
        }
    }
    return $false
}

function Check-Permissions {
    param(
        [string]$Path,
        [bool]$IsDirectory
    )

    if (Test-PathExcluded $Path) {
        return
    }

    if (-not (Test-Path $Path)) {
        return
    }

    try {
        $acl = Get-Acl $Path -ErrorAction SilentlyContinue
        if (-not $acl) {
            return
        }

        $currentUser = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
        $hasAccess = $false
        $hasWrite = $false

        foreach ($accessRule in $acl.Access) {
            if ($accessRule.IdentityReference -eq $currentUser -or
                $accessRule.IdentityReference -like "*\$env:USERNAME" -or
                $accessRule.IdentityReference -like "*\Users" -or
                $accessRule.IdentityReference -like "*\Authenticated Users") {

                if ($accessRule.FileSystemRights -band [System.Security.AccessControl.FileSystemRights]::ReadAndExecute) {
                    $hasAccess = $true
                }
                if ($accessRule.FileSystemRights -band [System.Security.AccessControl.FileSystemRights]::Write) {
                    $hasWrite = $true
                }
            }
        }

        if ($IsDirectory) {
            $checked.Directories++
            if (-not $hasAccess) {
                $errors += [PSCustomObject]@{
                    Type = "Dossier"
                    Path = $Path
                    Issue = "Pas d'accès en lecture"
                }
            }
        } else {
            $checked.Files++
            if (-not $hasAccess) {
                $errors += [PSCustomObject]@{
                    Type = "Fichier"
                    Path = $Path
                    Issue = "Pas d'accès en lecture"
                }
            }
        }

        # Vérifier les dossiers spéciaux qui doivent être en écriture
        if ($IsDirectory -and ($Path -like "*\storage\*" -or $Path -like "*\bootstrap\cache\*")) {
            if (-not $hasWrite) {
                $warnings += [PSCustomObject]@{
                    Type = "Dossier"
                    Path = $Path
                    Issue = "Pas d'acces en ecriture (requis pour Laravel)"
                }
            }
        }

    } catch {
        # Ignorer les erreurs d'accès
    }
}

Write-Host "Analyse des dossiers..." -ForegroundColor Yellow
$directories = Get-ChildItem -Path . -Recurse -Directory -Force -ErrorAction SilentlyContinue | Select-Object -First 200

foreach ($dir in $directories) {
    Check-Permissions -Path $dir.FullName -IsDirectory $true
}

Write-Host "Analyse des fichiers..." -ForegroundColor Yellow
$files = Get-ChildItem -Path . -Recurse -File -Force -ErrorAction SilentlyContinue | Select-Object -First 500

foreach ($file in $files) {
    Check-Permissions -Path $file.FullName -IsDirectory $false
}

# Vérifier les dossiers critiques
Write-Host ""
Write-Host "Vérification des dossiers critiques pour Laravel..." -ForegroundColor Yellow

$criticalDirs = @(
    "storage",
    "storage\app",
    "storage\framework",
    "storage\framework\cache",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs",
    "bootstrap\cache"
)

foreach ($dir in $criticalDirs) {
    if (Test-Path $dir) {
        try {
            $testFile = Join-Path $dir "test_write_$(Get-Random).tmp"
            $null = "test" | Out-File $testFile -ErrorAction Stop
            Remove-Item $testFile -ErrorAction SilentlyContinue
            Write-Host "  OK $dir - Accessible en ecriture" -ForegroundColor Green
        } catch {
            Write-Host "  ERREUR $dir - PAS accessible en ecriture" -ForegroundColor Red
            $warnings += [PSCustomObject]@{
                Type = "Dossier critique"
                Path = $dir
                    Issue = "Pas d'acces en ecriture"
            }
        }
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  RÉSUMÉ" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Dossiers vérifiés: $($checked.Directories)" -ForegroundColor White
Write-Host "Fichiers vérifiés: $($checked.Files)" -ForegroundColor White
Write-Host ""

if ($errors.Count -eq 0 -and $warnings.Count -eq 0) {
    Write-Host "OK Toutes les permissions sont correctes !" -ForegroundColor Green
} else {
    if ($errors.Count -gt 0) {
        Write-Host "ERREUR $($errors.Count) probleme(s) trouve(s) :" -ForegroundColor Red
        Write-Host ""
        foreach ($error in $errors | Select-Object -First 20) {
            Write-Host "  - [$($error.Type)] $($error.Path)" -ForegroundColor Red
            Write-Host "    Problème: $($error.Issue)" -ForegroundColor Yellow
        }
        if ($errors.Count -gt 20) {
            Write-Host "  ... et $($errors.Count - 20) autre(s) problème(s)" -ForegroundColor Yellow
        }
        Write-Host ""
    }

    if ($warnings.Count -gt 0) {
        Write-Host "ATTENTION $($warnings.Count) avertissement(s) :" -ForegroundColor Yellow
        Write-Host ""
        foreach ($warning in $warnings) {
            Write-Host "  - [$($warning.Type)] $($warning.Path)" -ForegroundColor Yellow
            Write-Host "    $($warning.Issue)" -ForegroundColor Yellow
        }
        Write-Host ""
    }
}

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

