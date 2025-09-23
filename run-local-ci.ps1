# -----------------------------------------------------------------------------
#   Local CI Simulation Script (run-local-ci.ps1)
#   This script mimics the GitHub Actions workflow to find all errors at once.
# -----------------------------------------------------------------------------

# Stop on first error
$ErrorActionPreference = "Stop"

# --- Header ---
Write-Host "🚀 Starting Local CI Simulation..." -ForegroundColor Yellow

# --- 1. Clear Laravel Cache ---
Write-Host "🧹 Clearing Laravel Caches..." -ForegroundColor Cyan
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# --- 2. Run PHPStan ---
Write-Host "📊 Running PHPStan Analysis (Level 9)..." -ForegroundColor Cyan
$phpstanOutput = ""
$phpstanExitCode = 0
try {
    ./vendor/bin/phpstan analyse --memory-limit=2G --no-progress | Tee-Object -Variable phpstanOutput
} catch {
    $phpstanExitCode = $LASTEXITCODE
}

if ($phpstanExitCode -ne 0) {
    Write-Host "❌ PHPStan Failed! See full report below." -ForegroundColor Red
    Write-Host $phpstanOutput
    exit 1
} else {
    Write-Host "✅ PHPStan Passed!" -ForegroundColor Green
}

# --- 3. Run PHPUnit ---
Write-Host "🧪 Running PHPUnit Tests (with Coverage)..." -ForegroundColor Cyan
$phpunitOutput = ""
$phpunitExitCode = 0
try {
    # This command now matches the GitHub Actions workflow
    ./vendor/bin/phpunit --coverage-clover=coverage.xml | Tee-Object -Variable phpunitOutput
} catch {
    $phpunitExitCode = $LASTEXITCODE
}

if ($phpunitExitCode -ne 0) {
    Write-Host "❌ PHPUnit Failed! See full report below." -ForegroundColor Red
    Write-Host $phpunitOutput
    exit 1
} else {
    Write-Host "✅ PHPUnit Passed!" -ForegroundColor Green
}

# --- Footer ---
Write-Host "🎉🎉🎉 ALL CHECKS PASSED LOCALLY! You are ready to push." -ForegroundColor Green
