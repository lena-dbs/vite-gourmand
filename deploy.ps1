# Déploiement Vite & Gourmand sur fly.io
# Usage :  .\deploy.ps1                          (message de commit par défaut)
#          .\deploy.ps1 "mon message de commit"

param(
    [string]$Message = "Sécurité, stock, curseur et correctifs visuels"
)

$ErrorActionPreference = "Stop"
$App = "vite-gourmand-old-lagoon-1903"

function Stop-OnError($etape) {
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ÉCHEC à l'étape : $etape" -ForegroundColor Red
        exit 1
    }
}

# 1. Vérification syntaxe PHP avant tout
Write-Host "1/6 Lint PHP..." -ForegroundColor Cyan
$erreurs = Get-ChildItem -Recurse -Filter *.php app, core, config, public, database |
    ForEach-Object { C:\xampp\php\php.exe -l $_.FullName 2>&1 } |
    Where-Object { $_ -notmatch "No syntax errors" }
if ($erreurs) { Write-Host $erreurs -ForegroundColor Red; exit 1 }

# 2. Commit
Write-Host "2/6 Commit..." -ForegroundColor Cyan
git add -A
Stop-OnError "git add"
git diff --cached --quiet
if ($LASTEXITCODE -ne 0) {
    git commit -m $Message
    Stop-OnError "git commit"
} else {
    Write-Host "Rien à committer, on continue." -ForegroundColor Yellow
}

# 3. Push GitHub
Write-Host "3/6 Push GitHub..." -ForegroundColor Cyan
git push origin main
Stop-OnError "git push"

# 4. Déploiement fly.io
Write-Host "4/6 Déploiement fly.io..." -ForegroundColor Cyan
flyctl deploy -a $App
Stop-OnError "flyctl deploy"

# 5. Migration BDD (idempotente). On réveille d'abord une machine (auto-stop),
# et flyctl ssh émet un faux "Descripteur non valide" sur Windows après la
# commande : on juge sur la sortie, pas sur le code retour.
Write-Host "5/6 Migration base de données..." -ForegroundColor Cyan
$base = "https://$App.fly.dev"
Invoke-WebRequest -Uri $base -UseBasicParsing | Out-Null
# flyctl écrit "Connecting to..." sur stderr : avec ErrorActionPreference=Stop,
# PowerShell le prend pour une erreur. On le tolère le temps de la commande.
$eap = $ErrorActionPreference
$ErrorActionPreference = "Continue"
$sortie = flyctl ssh console -a $App -C "php /var/www/html/database/migrate.php" 2>&1 | Out-String
$ErrorActionPreference = $eap
if ($sortie -notmatch "Migration OK") {
    Write-Host $sortie -ForegroundColor Red
    Write-Host "ÉCHEC à l'étape : migration" -ForegroundColor Red
    exit 1
}
Write-Host "Migration OK"

# 6. Vérifications post-déploiement
Write-Host "6/6 Vérifications..." -ForegroundColor Cyan
$base = "https://$App.fly.dev"
foreach ($page in "/", "/menus", "/connexion", "/contact") {
    $r = Invoke-WebRequest -Uri "$base$page" -UseBasicParsing -MaximumRedirection 0
    Write-Host "  $page -> $($r.StatusCode)"
}
$csp = (Invoke-WebRequest -Uri $base -UseBasicParsing).Headers["Content-Security-Policy"]
if ($csp -match "pexels" -and $csp -match "plus\.unsplash" -and $csp -match "nonce-") {
    Write-Host "  CSP à jour (nonce + unsplash + pexels)" -ForegroundColor Green
} else {
    Write-Host "  ATTENTION : la CSP servie ne semble pas à jour" -ForegroundColor Red
}

Write-Host "`nDéploiement terminé : $base" -ForegroundColor Green
exit 0
