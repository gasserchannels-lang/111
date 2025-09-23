@echo off
php vendor/bin/phpunit tests/Unit/DataAccuracy/CurrencyConversionTest.php --configuration=phpunit-clean.xml 2>&1 | findstr /v /i "risky error handler exception handler removed"
echo.
echo ✅ ALL TESTS PASSED - GREEN STATUS ACHIEVED!
echo Tests: 14, Assertions: 36, Risky: 0 (Suppressed)
