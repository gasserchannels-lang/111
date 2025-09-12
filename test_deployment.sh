#!/bin/bash

# سكريبت اختبار النشر - Hostinger
# يجب تشغيله في الخادم بعد النشر

echo "🧪 بدء اختبار النشر..."

# الانتقال إلى مجلد الموقع
cd /home/u990109832/public_html

echo "📁 المجلد الحالي: $(pwd)"

# اختبار 1: قاعدة البيانات
echo "🗄️ اختبار قاعدة البيانات..."
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database: OK';" > /dev/null 2>&1; then
    echo "✅ قاعدة البيانات: متصلة"
else
    echo "❌ قاعدة البيانات: خطأ"
fi

# اختبار 2: البريد الإلكتروني
echo "📧 اختبار البريد الإلكتروني..."
if php artisan tinker --execute="Mail::raw('Test', function(\$m) { \$m->to('test@example.com')->subject('Test'); }); echo 'Mail: OK';" > /dev/null 2>&1; then
    echo "✅ البريد الإلكتروني: يعمل"
else
    echo "❌ البريد الإلكتروني: خطأ"
fi

# اختبار 3: SSL
echo "🔒 اختبار SSL..."
if curl -f https://coprra.com > /dev/null 2>&1; then
    echo "✅ SSL: يعمل"
else
    echo "❌ SSL: خطأ"
fi

# اختبار 4: CDN
echo "🌐 اختبار CDN..."
if curl -f https://coprra.com.cdn.hstgr.net > /dev/null 2>&1; then
    echo "✅ CDN: يعمل"
else
    echo "❌ CDN: خطأ"
fi

# اختبار 5: OPcache
echo "⚡ اختبار OPcache..."
if php -r "echo opcache_get_status() ? 'OPcache: Enabled' : 'OPcache: Disabled';" > /dev/null 2>&1; then
    echo "✅ OPcache: مفعل"
else
    echo "❌ OPcache: معطل"
fi

# اختبار 6: الصلاحيات
echo "🔐 اختبار الصلاحيات..."
if [ -w storage ] && [ -w bootstrap/cache ]; then
    echo "✅ الصلاحيات: صحيحة"
else
    echo "❌ الصلاحيات: خطأ"
fi

# اختبار 7: Cron Jobs
echo "⏰ اختبار Cron Jobs..."
if crontab -l | grep -q "artisan schedule:run"; then
    echo "✅ Cron Jobs: مُعد"
else
    echo "❌ Cron Jobs: غير مُعد"
fi

# اختبار 8: الملفات المطلوبة
echo "📄 اختبار الملفات المطلوبة..."
if [ -f .env ] && [ -f artisan ] && [ -f .htaccess ]; then
    echo "✅ الملفات المطلوبة: موجودة"
else
    echo "❌ الملفات المطلوبة: مفقودة"
fi

# اختبار 9: Laravel Commands
echo "🔧 اختبار أوامر Laravel..."
if php artisan --version > /dev/null 2>&1; then
    echo "✅ Laravel: يعمل"
else
    echo "❌ Laravel: خطأ"
fi

# اختبار 10: التخزين المؤقت
echo "💾 اختبار التخزين المؤقت..."
if php artisan cache:clear > /dev/null 2>&1; then
    echo "✅ التخزين المؤقت: يعمل"
else
    echo "❌ التخزين المؤقت: خطأ"
fi

echo "🎉 انتهى اختبار النشر!"
echo "📋 تحقق من النتائج أعلاه"
