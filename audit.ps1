# PowerShell Comprehensive Audit Script - v6.0
# 1. Initialization
$ReportFile = "PROJECT_HEALTH_AUDIT_REPORT.md"
$StartTime = Get-Date
Set-Content -Path $ReportFile -Value "# Project Health Audit Report (v6.0)"
Add-Content -Path $ReportFile -Value "Generated on: $($StartTime.ToString('yyyy-MM-dd HH:mm:ss'))"
Add-Content -Path $ReportFile -Value ""
Add-Content -Path $ReportFile -Value "This report provides a comprehensive analysis of the project's health, including testing, code quality, and security."
Add-Content -Path $ReportFile -Value ""
Add-Content -Path $ReportFile -Value "---"

# Clear Laravel's configuration cache to ensure test environment variables are loaded
Write-Host "🚀 Clearing Laravel config cache..."
php artisan config:clear

# Helper function to run a command and append its output to the report
function Run-AuditStep {
    param(
        [string]$Title,
        [string]$Command,
        [string]$ReportFile
    )
    Write-Host "🚀 Starting: $Title"
    Add-Content -Path $ReportFile -Value "## $Title"
    try {
        $output = Invoke-Expression -Command $Command 2>&1
        $exitCode = $LASTEXITCODE
        if ($exitCode -ne 0) {
            Write-Host "❌ FAILED: $Title" -ForegroundColor Red
            Add-Content -Path $ReportFile -Value "### ❌ Status: FAILED"
            Add-Content -Path $ReportFile -Value "``````powershell`n$($output | Out-String)``````"
        } else {
            Write-Host "✅ PASSED: $Title" -ForegroundColor Green
            Add-Content -Path $ReportFile -Value "### ✅ Status: PASSED"
            # Optionally, add success output if needed
            # Add-Content -Path $ReportFile -Value "``````powershell`n$($output | Out-String)``````"
        }
    } catch {
        Write-Host "🔥 CRITICAL ERROR in: $Title" -ForegroundColor DarkRed
        Add-Content -Path $ReportFile -Value "### 🔥 Status: CRITICAL SCRIPT ERROR"
        Add-Content -Path $ReportFile -Value "The command failed to execute. Error:"
        Add-Content -Path $ReportFile -Value "``````powershell`n$($_.Exception.Message)``````"
    }
    Add-Content -Path $ReportFile -Value ""
    Add-Content -Path $ReportFile -Value "---"
    Write-Host "Finished: $Title"
    Start-Sleep -Milliseconds 50 # Add a small delay to prevent file lock issues
    Write-Host ""
}

# 2. Define and Run Audit Steps
# Each step is a different quality check.

# Step 1: Run all PHPUnit tests
Run-AuditStep -Title "PHPUnit Tests" -Command "php vendor/bin/phpunit" -ReportFile $ReportFile

# Step 2: Check for code style violations with Pint
Run-AuditStep -Title "Code Style (Pint)" -Command "php vendor/bin/pint --test" -ReportFile $ReportFile

# Step 3: Run static analysis with PHPStan to find potential bugs
Run-AuditStep -Title "Static Analysis (PHPStan)" -Command "php vendor/bin/phpstan analyse" -ReportFile $ReportFile

# Step 4: Run security check for known vulnerabilities in dependencies
Run-AuditStep -Title "Dependency Security (Composer Audit)" -Command "composer audit" -ReportFile $ReportFile

# Step 5: Run an additional application security check with Enlightn
Run-AuditStep -Title "Security Checker (Enlightn)" -Command "php vendor/bin/security-checker security:check --end-point=http://security.symfony.com/check_lock" -ReportFile $ReportFile

# 3. Finalization
$EndTime = Get-Date
$Duration = New-TimeSpan -Start $StartTime -End $EndTime
Add-Content -Path $ReportFile -Value "## Audit Summary"
Add-Content -Path $ReportFile -Value "Audit completed in: $($Duration.TotalSeconds) seconds."
Write-Host "✅✅✅ Comprehensive audit finished. Check the report file: $ReportFile"
