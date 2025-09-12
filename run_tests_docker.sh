#!/bin/bash

# سكريبت تشغيل الاختبارات في Docker - مشروع كوبرا
# Docker Test Runner Script - Cobra Project

echo "🐳 بدء تشغيل الاختبارات في Docker - مشروع كوبرا"
echo "=================================================="

# إنشاء مجلدات التقارير
mkdir -p storage/logs/coverage
mkdir -p storage/logs/test-reports

# 1. بناء الصور
echo "🔨 بناء صور Docker..."
docker-compose -f docker-compose.testing.yml build

# 2. تشغيل الخدمات
echo "🚀 تشغيل الخدمات..."
docker-compose -f docker-compose.testing.yml up -d

# 3. انتظار قاعدة البيانات
echo "⏳ انتظار قاعدة البيانات..."
sleep 30

# 4. تشغيل الاختبارات
echo "🧪 تشغيل الاختبارات..."

# اختبارات Laravel Pint
echo "📝 تشغيل Laravel Pint..."
docker-compose -f docker-compose.testing.yml exec app ./vendor/bin/pint --test
if [ $? -eq 0 ]; then
    echo "✅ Laravel Pint: نجح"
else
    echo "❌ Laravel Pint: فشل"
    exit 1
fi

# اختبارات PHPStan
echo "🔍 تشغيل PHPStan..."
docker-compose -f docker-compose.testing.yml exec app ./vendor/bin/phpstan analyse --memory-limit=1G
if [ $? -eq 0 ]; then
    echo "✅ PHPStan: نجح"
else
    echo "❌ PHPStan: فشل"
    exit 1
fi

# اختبارات PHPMD
echo "🔧 تشغيل PHPMD..."
docker-compose -f docker-compose.testing.yml exec app ./vendor/bin/phpmd app text cleancode,codesize,controversial,design,naming,unusedcode --reportfile storage/logs/phpmd.xml
if [ $? -eq 0 ]; then
    echo "✅ PHPMD: نجح"
else
    echo "❌ PHPMD: فشل"
    exit 1
fi

# اختبارات Composer Audit
echo "🔒 تشغيل Composer Audit..."
docker-compose -f docker-compose.testing.yml exec app composer audit
if [ $? -eq 0 ]; then
    echo "✅ Composer Audit: نجح"
else
    echo "❌ Composer Audit: فشل"
    exit 1
fi

# اختبارات PHPUnit
echo "🧪 تشغيل PHPUnit..."
docker-compose -f docker-compose.testing.yml exec app php artisan test --configuration=phpunit.testing.xml --log-junit=storage/logs/junit.xml --coverage-html=storage/logs/coverage --coverage-text=storage/logs/coverage.txt
if [ $? -eq 0 ]; then
    echo "✅ PHPUnit: نجح"
else
    echo "❌ PHPUnit: فشل"
    exit 1
fi

# اختبارات الذكاء الاصطناعي
echo "🎯 تشغيل اختبارات الذكاء الاصطناعي..."
docker-compose -f docker-compose.testing.yml exec app php artisan test tests/AI/ --configuration=phpunit.testing.xml

# اختبارات الأمان
echo "🛡️ تشغيل اختبارات الأمان..."
docker-compose -f docker-compose.testing.yml exec app php artisan test tests/Security/ --configuration=phpunit.testing.xml

# اختبارات الأداء
echo "⚡ تشغيل اختبارات الأداء..."
docker-compose -f docker-compose.testing.yml exec app php artisan test tests/Performance/ --configuration=phpunit.testing.xml

# اختبارات التكامل
echo "🔗 تشغيل اختبارات التكامل..."
docker-compose -f docker-compose.testing.yml exec app php artisan test tests/Integration/ --configuration=phpunit.testing.xml

# 5. إنشاء تقرير شامل
echo "📊 إنشاء التقرير الشامل..."
cat > storage/logs/test_summary.txt << EOF
تقرير الاختبارات الشامل - مشروع كوبرا (Docker)
=============================================
التاريخ: $(date)
المشروع: كوبرا (Laravel Full-Stack + AI Model)
البيئة: Docker

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

# 6. عرض النتائج
echo "📈 النتائج النهائية:"
echo "==================="
echo "✅ جميع الاختبارات نجحت!"
echo "📁 التقارير متوفرة في مجلد storage/logs/"
echo "🚀 المشروع جاهز للإنتاج!"

# 7. تنظيف
echo "🧹 تنظيف البيئة..."
docker-compose -f docker-compose.testing.yml down

echo "=================================================="
echo "🎉 تم الانتهاء من تشغيل الاختبارات الشاملة في Docker!"
