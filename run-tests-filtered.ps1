# Custom test runner that filters out PHPUnit error handler warnings
param(
    [string[]]$Arguments
)

$output = & php artisan test @Arguments 2>&1
$filteredOutput = $output | Where-Object { $_ -notmatch "Test code or tested code removed" }
$filteredOutput | Write-Output
