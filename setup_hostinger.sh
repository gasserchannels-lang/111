#!/bin/bash

# سكريبت إعداد Hostinger للمشروع
# يجب تشغيله في الخادم بعد رفع الملفات

echo "🚀 بدء إعداد Hostinger للمشروع..."

# الانتقال إلى مجلد الموقع
cd /home/u990109832/public_html

echo "📁 الانتقال إلى مجلد الموقع: $(pwd)"

# إنشاء نسخة احتياطية
echo "💾 إنشاء نسخة احتياطية..."
cp -r . ../backup_$(date +%Y%m%d_%H%M%S)

# إعداد الصلاحيات
echo "🔐 إعداد صلاحيات الملفات..."
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod 644 .env
chmod 644 .htaccess
chmod 644 artisan

# تنظيف التخزين المؤقت
echo "🧹 تنظيف التخزين المؤقت..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# إنشاء التخزين المؤقت
echo "⚡ إنشاء التخزين المؤقت..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# إعداد قاعدة البيانات
echo "🗄️ إعداد قاعدة البيانات..."
php artisan migrate --force

# إعداد Seeders (اختياري)
# php artisan db:seed --force

# اختبار الموقع
echo "🧪 اختبار الموقع..."
if curl -f https://coprra.com > /dev/null 2>&1; then
    echo "✅ الموقع يعمل بشكل صحيح"
else
    echo "❌ هناك مشكلة في الموقع"
fi

# اختبار API
echo "🔌 اختبار API..."
if curl -f https://coprra.com/api/health > /dev/null 2>&1; then
    echo "✅ API يعمل بشكل صحيح"
else
    echo "⚠️ API غير متاح أو يحتاج إعداد"
fi

echo "🎉 تم إعداد Hostinger بنجاح!"
echo "📋 الخطوات التالية:"
echo "1. إعداد Cron Jobs في لوحة التحكم"
echo "2. إعداد GitHub Actions"
echo "3. اختبار جميع الوظائف"
