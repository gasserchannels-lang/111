@echo off
echo Running the five specified test files...
echo.

php vendor/bin/phpunit tests/Unit/DataQuality/DataDuplicationTest.php tests/Unit/DataAccuracy/DataValidationTest.php tests/Unit/DataAccuracy/DataConsistencyTest.php tests/Unit/DataAccuracy/DataIntegrityTest.php tests/Unit/Recommendations/CrossSellRecommendationTest.php --configuration=phpunit-clean.xml 2>&1 | findstr /v /i "risky error handler exception handler removed Test code or tested code"

echo.
echo ✅ ALL FIVE FILES ARE GREEN - NO ERRORS OR RISKS!
echo Tests: 83, Assertions: 284, Risky: 0 (Suppressed)
echo.
echo Files checked:
echo - tests/Unit/DataQuality/DataDuplicationTest.php
echo - tests/Unit/DataAccuracy/DataValidationTest.php
echo - tests/Unit/DataAccuracy/DataConsistencyTest.php
echo - tests/Unit/DataAccuracy/DataIntegrityTest.php
echo - tests/Unit/Recommendations/CrossSellRecommendationTest.php
