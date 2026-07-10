# =============================================================================
# Préparation déploiement LWS depuis Windows (push GitHub)
# Usage : .\deploy-lws.ps1 -Message "Description de la mise à jour"
# =============================================================================

param(
    [string]$Message = "Mise à jour production LWS",
    [string]$Branch = "upgrade/laravel-11"
)

$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ProjectRoot

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  PUSH VERS GITHUB — $Branch" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

$status = git status --porcelain 2>&1
if (-not $status) {
    Write-Host "Aucun changement local à committer." -ForegroundColor Yellow
} else {
    git add -A -- . ":(exclude)storage/framework/sessions" ":(exclude)storage/framework/sessions/**" ":(exclude)storage/logs" ":(exclude)storage/logs/**" ":(exclude)storage/framework/cache" ":(exclude)storage/framework/cache/**" ":(exclude)storage/framework/views" ":(exclude)storage/framework/views/**" ":(exclude).env"
    git commit -m $Message
}

git push origin $Branch

Write-Host ""
Write-Host "Push terminé. Sur le serveur LWS (SSH) :" -ForegroundColor Green
Write-Host "  cd ~/public_html/thevie" -ForegroundColor White
Write-Host "  bash deploy-lws.sh" -ForegroundColor White
Write-Host ""
