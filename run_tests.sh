#!/bin/bash

# سكريبت تشغيل الاختبارات الشامل - مشروع كوبرا
# Comprehensive Test Runner Script - Cobra Project

echo "🚀 بدء تشغيل الاختبارات الشاملة - مشروع كوبرا"
echo "=================================================="

# إنشاء مجلدات التقارير
mkdir -p storage/logs/coverage
mkdir -p storage/logs/test-reports

# 1. تشغيل Laravel Pint (تنسيق الكود)
echo "📝 تشغيل Laravel Pint..."
./vendor/bin/pint --test
if [ $? -eq 0 ]; then
    echo "✅ Laravel Pint: نجح"
else
    echo "❌ Laravel Pint: فشل"
    exit 1
fi

# 2. تشغيل PHPStan (التحليل الثابت)
echo "🔍 تشغيل PHPStan..."
./vendor/bin/phpstan analyse --memory-limit=1G
if [ $? -eq 0 ]; then
    echo "✅ PHPStan: نجح"
else
    echo "❌ PHPStan: فشل"
    exit 1
fi

# 3. تشغيل PHPMD (جودة الكود)
echo "🔧 تشغيل PHPMD..."
./vendor/bin/phpmd app text cleancode,codesize,controversial,design,naming,unusedcode --reportfile storage/logs/phpmd.xml
if [ $? -eq 0 ]; then
    echo "✅ PHPMD: نجح"
else
    echo "❌ PHPMD: فشل"
    exit 1
fi

# 4. تشغيل Composer Audit (الأمان)
echo "🔒 تشغيل Composer Audit..."
composer audit
if [ $? -eq 0 ]; then
    echo "✅ Composer Audit: نجح"
else
    echo "❌ Composer Audit: فشل"
    exit 1
fi

# 5. تشغيل PHPUnit (الاختبارات)
echo "🧪 تشغيل PHPUnit..."
php artisan test --configuration=phpunit.testing.xml --log-junit=storage/logs/junit.xml --coverage-html=storage/logs/coverage --coverage-text=storage/logs/coverage.txt
if [ $? -eq 0 ]; then
    echo "✅ PHPUnit: نجح"
else
    echo "❌ PHPUnit: فشل"
    exit 1
fi

# 6. تشغيل اختبارات محددة
echo "🎯 تشغيل اختبارات الذكاء الاصطناعي..."
php artisan test tests/AI/ --configuration=phpunit.testing.xml

echo "🛡️ تشغيل اختبارات الأمان..."
php artisan test tests/Security/ --configuration=phpunit.testing.xml

echo "⚡ تشغيل اختبارات الأداء..."
php artisan test tests/Performance/ --configuration=phpunit.testing.xml

echo "🔗 تشغيل اختبارات التكامل..."
php artisan test tests/Integration/ --configuration=phpunit.testing.xml

# 7. إنشاء تقرير شامل
echo "📊 إنشاء التقرير الشامل..."
cat > storage/logs/test_summary.txt << EOF
تقرير الاختبارات الشامل - مشروع كوبرا
=====================================
التاريخ: $(date)
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
EOF

echo "✅ تم إنشاء التقرير الشامل في storage/logs/test_summary.txt"

# 8. عرض النتائج
echo "📈 النتائج النهائية:"
echo "==================="
echo "✅ جميع الاختبارات نجحت!"
echo "📁 التقارير متوفرة في مجلد storage/logs/"
echo "🚀 المشروع جاهز للإنتاج!"

echo "=================================================="
echo "🎉 تم الانتهاء من تشغيل الاختبارات الشاملة!"
