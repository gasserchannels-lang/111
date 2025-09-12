# إعداد صلاحيات الملفات - Hostinger

## الصلاحيات المطلوبة

### 1. مجلدات Laravel:
```bash
# المجلدات الأساسية
chmod -R 755 /home/u990109832/public_html
chmod -R 775 /home/u990109832/public_html/storage
chmod -R 775 /home/u990109832/public_html/bootstrap/cache
```

### 2. ملفات Laravel:
```bash
# ملفات التكوين
chmod 644 /home/u990109832/public_html/.env
chmod 644 /home/u990109832/public_html/.htaccess
chmod 644 /home/u990109832/public_html/artisan
```

### 3. ملفات التطبيق:
```bash
# ملفات PHP
find /home/u990109832/public_html -name "*.php" -exec chmod 644 {} \;
find /home/u990109832/public_html -name "*.js" -exec chmod 644 {} \;
find /home/u990109832/public_html -name "*.css" -exec chmod 644 {} \;
```

## سكريبت إعداد الصلاحيات:

### ملف `setup_permissions.sh`:
```bash
#!/bin/bash

# الانتقال إلى مجلد الموقع
cd /home/u990109832/public_html

# إعداد صلاحيات المجلدات
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# إعداد صلاحيات الملفات
chmod 644 .env
chmod 644 .htaccess
chmod 644 artisan

# إعداد صلاحيات الملفات العامة
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.js" -exec chmod 644 {} \;
find . -type f -name "*.css" -exec chmod 644 {} \;
find . -type f -name "*.html" -exec chmod 644 {} \;

# إعداد صلاحيات المجلدات
find . -type d -exec chmod 755 {} \;

echo "تم إعداد الصلاحيات بنجاح"
```

## خطوات الإعداد:

### 1. عبر SSH:
```bash
# الاتصال بالخادم
ssh -p 65002 u990109832@45.87.81.218

# الانتقال إلى مجلد الموقع
cd /home/u990109832/public_html

# تشغيل سكريبت الصلاحيات
chmod +x setup_permissions.sh
./setup_permissions.sh
```

### 2. عبر File Manager:
1. اذهب إلى "الملفات" في لوحة التحكم
2. انتقل إلى `public_html`
3. اضغط كليك يمين على `storage`
4. اختر "Permissions"
5. اضبط على `775`

## اختبار الصلاحيات:

### 1. اختبار الكتابة:
```bash
# اختبار كتابة في storage
touch /home/u990109832/public_html/storage/test.txt
rm /home/u990109832/public_html/storage/test.txt
```

### 2. اختبار القراءة:
```bash
# اختبار قراءة الملفات
cat /home/u990109832/public_html/.env
```

## استكشاف الأخطاء:

### 1. مشاكل الكتابة:
```bash
# تحقق من الصلاحيات
ls -la /home/u990109832/public_html/storage
```

### 2. مشاكل القراءة:
```bash
# تحقق من الصلاحيات
ls -la /home/u990109832/public_html/.env
```

## نصائح مهمة:

1. **تأكد من المسار الصحيح**: `/home/u990109832/public_html`
2. **استخدم الصلاحيات المناسبة**: 755 للمجلدات، 644 للملفات
3. **اختبر الصلاحيات**: بعد إعدادها
4. **احتفظ بنسخة احتياطية**: قبل تغيير الصلاحيات
