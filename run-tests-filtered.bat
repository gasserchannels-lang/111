@echo off
REM Custom test runner that filters out PHPUnit error handler warnings
php artisan test %* 2>&1 | findstr /v "Test code or tested code removed"
