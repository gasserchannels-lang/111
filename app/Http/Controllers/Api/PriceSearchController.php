#!/bin/bash
# Digital Forensics Script v1.0
# يركز على تشغيل الاختبارات الفاشلة وجمع كل السجلات الممكنة.

set -e # توقف عند أول خطأ في الإعداد

# --- الإعدادات ---
SANDBOX_DIR="/tmp/sandbox"
REPORT_FILE="/tmp/reports/forensics_report_$(date +%Y%m%d_%H%M%S).txt"
LARAVEL_LOG_FILE="$SANDBOX_DIR/storage/logs/laravel.log"
mkdir -p /tmp/reports

# --- بداية التقرير ---
echo "=== 🕵️‍♂️ تقرير التحقيق الجنائي الرقمي ===" > "$REPORT_FILE"
date >> "$REPORT_FILE"
cd "$SANDBOX_DIR"

# --- تجهيز البيئة ---
echo -e "\n--- تجهيز البيئة ---" >> "$REPORT_FILE"
# تأكد من أن ملف .env موجود قبل تعديله
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=:memory:/' .env
php artisan config:clear
php artisan cache:clear
php artisan migrate --force
echo "✅ تم تجهيز البيئة." >> "$REPORT_FILE"

# --- مسح السجلات القديمة لبدء تحقيق نظيف ---
echo -e "\n--- مسح السجلات القديمة ---" >> "$REPORT_FILE"
rm -f "$LARAVEL_LOG_FILE"
touch "$LARAVEL_LOG_FILE"
chmod 777 "$LARAVEL_LOG_FILE"
echo "✅ تم مسح سجلات Laravel القديمة." >> "$REPORT_FILE"

# --- تشغيل الاختبارات الفاشلة ---
echo -e "\n--- بدء تشغيل PHPUnit (سيتم تسجيل الأخطاء) ---" >> "$REPORT_FILE"
# اسمح للسكريبت بالاستمرار حتى لو فشلت الاختبارات
set +e
vendor/bin/phpunit --filter "PriceSearchControllerTest" >> "$REPORT_FILE" 2>&1
echo "✅ انتهى تشغيل PHPUnit." >> "$REPORT_FILE"

# --- جمع الأدلة (السجلات) ---
echo -e "\n\n=======================================================" >> "$REPORT_FILE"
echo "🔬🔬🔬 الأدلة التي تم جمعها (سجلات Laravel) 🔬🔬🔬" >> "$REPORT_FILE"
echo "=======================================================" >> "$REPORT_FILE"

# انتظر لحظة للتأكد من أن كل شيء قد تم كتابته إلى السجل
sleep 2

if [ -f "$LARAVEL_LOG_FILE" ]; then
    cat "$LARAVEL_LOG_FILE" >> "$REPORT_FILE"
else
    echo "⚠️ لم يتم العثور على ملف سجلات Laravel في $LARAVEL_LOG_FILE" >> "$REPORT_FILE"
fi

echo -e "\n\n=======================================================" >> "$REPORT_FILE"
echo "🎉 انتهى التحقيق بنجاح!" >> "$REPORT_FILE"
exit 0
