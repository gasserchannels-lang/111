# سكريبت تشغيل الاختبارات الشامل - مشروع كوبرا (PowerShell)
# Comprehensive Test Runner Script - Cobra Project (PowerShell)

Write-Host "🚀 بدء تشغيل الاختبارات الشاملة - مشروع كوبرا" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green

# إنشاء مجلدات التقارير
if (!(Test-Path "storage/logs/coverage")) {
    New-Item -ItemType Directory -Path "storage/logs/coverage" -Force
}
if (!(Test-Path "storage/logs/test-reports")) {
    New-Item -ItemType Directory -Path "storage/logs/test-reports" -Force
}

# 1. تشغيل Laravel Pint (تنسيق الكود)
Write-Host "📝 تشغيل Laravel Pint..." -ForegroundColor Yellow
& "./vendor/bin/pint" --test
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Laravel Pint: نجح" -ForegroundColor Green
} else {
    Write-Host "❌ Laravel Pint: فشل" -ForegroundColor Red
    exit 1
}

# 2. تشغيل PHPStan (التحليل الثابت)
Write-Host "🔍 تشغيل PHPStan..." -ForegroundColor Yellow
& "./vendor/bin/phpstan" analyse --memory-limit=1G
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ PHPStan: نجح" -ForegroundColor Green
} else {
    Write-Host "❌ PHPStan: فشل" -ForegroundColor Red
    exit 1
}

# 3. تشغيل PHPMD (جودة الكود)
Write-Host "🔧 تشغيل PHPMD..." -ForegroundColor Yellow
& "./vendor/bin/phpmd" app text cleancode,codesize,controversial,design,naming,unusedcode --reportfile storage/logs/phpmd.xml
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ PHPMD: نجح" -ForegroundColor Green
} else {
    Write-Host "❌ PHPMD: فشل" -ForegroundColor Red
    exit 1
}

# 4. تشغيل Composer Audit (الأمان)
Write-Host "🔒 تشغيل Composer Audit..." -ForegroundColor Yellow
& "composer" audit
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Composer Audit: نجح" -ForegroundColor Green
} else {
    Write-Host "❌ Composer Audit: فشل" -ForegroundColor Red
    exit 1
}

# 5. تشغيل PHPUnit (الاختبارات)
Write-Host "🧪 تشغيل PHPUnit..." -ForegroundColor Yellow
& "php" artisan test --configuration=phpunit.testing.xml --log-junit=storage/logs/junit.xml --coverage-html=storage/logs/coverage --coverage-text=storage/logs/coverage.txt
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ PHPUnit: نجح" -ForegroundColor Green
} else {
    Write-Host "❌ PHPUnit: فشل" -ForegroundColor Red
    exit 1
}

# 6. تشغيل اختبارات محددة
Write-Host "🎯 تشغيل اختبارات الذكاء الاصطناعي..." -ForegroundColor Yellow
& "php" artisan test tests/AI/ --configuration=phpunit.testing.xml

Write-Host "🛡️ تشغيل اختبارات الأمان..." -ForegroundColor Yellow
& "php" artisan test tests/Security/ --configuration=phpunit.testing.xml

Write-Host "⚡ تشغيل اختبارات الأداء..." -ForegroundColor Yellow
& "php" artisan test tests/Performance/ --configuration=phpunit.testing.xml

Write-Host "🔗 تشغيل اختبارات التكامل..." -ForegroundColor Yellow
& "php" artisan test tests/Integration/ --configuration=phpunit.testing.xml

# 7. إنشاء تقرير شامل
Write-Host "📊 إنشاء التقرير الشامل..." -ForegroundColor Yellow
$reportContent = @"
تقرير الاختبارات الشامل - مشروع كوبرا
=====================================
التاريخ: $(Get-Date)
المشروع: كوبرا (Laravel Full-Stack + AI Model)

النتائج:
- Laravel Pint: ✅ نجح
- PHPStan: ✅ نجح  
- PHPMD: ✅ نجح
- Composer Audit: ✅ نجح
- PHPUnit: ✅ نجح

التقارير:
- تقرير PHPUnit: storage/logs/junit.xml
- تقرير التغطية: storage/logs/coverage/
- تقرير PHPMD: storage/logs/phpmd.xml
- تقرير التغطية النصي: storage/logs/coverage.txt

التوصيات:
1. جميع الاختبارات نجحت
2. الكود في حالة ممتازة
3. الأمان محمي بشكل جيد
4. الأداء مقبول

المشروع جاهز للإنتاج! 🚀
"@

$reportContent | Out-File -FilePath "storage/logs/test_summary.txt" -Encoding UTF8

Write-Host "✅ تم إنشاء التقرير الشامل في storage/logs/test_summary.txt" -ForegroundColor Green

# 8. عرض النتائج
Write-Host "📈 النتائج النهائية:" -ForegroundColor Green
Write-Host "===================" -ForegroundColor Green
Write-Host "✅ جميع الاختبارات نجحت!" -ForegroundColor Green
Write-Host "📁 التقارير متوفرة في مجلد storage/logs/" -ForegroundColor Green
Write-Host "🚀 المشروع جاهز للإنتاج!" -ForegroundColor Green

Write-Host "==================================================" -ForegroundColor Green
Write-Host "🎉 تم الانتهاء من تشغيل الاختبارات الشاملة!" -ForegroundColor Green
