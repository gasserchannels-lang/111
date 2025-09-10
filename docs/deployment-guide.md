# 🚀 دليل النشر على Hostinger

## 📋 متطلبات الخادم

### الحد الأدنى للمتطلبات
- **PHP**: 8.2 أو أحدث
- **Composer**: 2.0 أو أحدث
- **Node.js**: 18.0 أو أحدث
- **npm**: 8.0 أو أحدث
- **MySQL**: 8.0 أو أحدث
- **Apache/Nginx**: مع mod_rewrite مفعل

### مساحة القرص المطلوبة
- **الحد الأدنى**: 500 MB
- **الموصى به**: 1 GB أو أكثر

## 🔧 خطوات النشر

### 1. إعداد قاعدة البيانات

```sql
-- إنشاء قاعدة بيانات جديدة
CREATE DATABASE coprra_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- إنشاء مستخدم جديد
CREATE USER 'coprra_user'@'localhost' IDENTIFIED BY 'strong_password_here';

-- منح الصلاحيات
GRANT ALL PRIVILEGES ON coprra_production.* TO 'coprra_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. رفع الملفات

```bash
# رفع جميع الملفات إلى المجلد الرئيسي
# استثناء: node_modules, vendor, .git
```

### 3. تثبيت التبعيات

```bash
# تثبيت تبعيات PHP
composer install --no-dev --optimize-autoloader

# تثبيت تبعيات Node.js
npm ci --only=production

# بناء الأصول
npm run build
```

### 4. إعداد متغيرات البيئة

```bash
# نسخ ملف البيئة
cp .env.example .env

# تحرير ملف .env
nano .env
```

### إعدادات .env للإنتاج

```env
APP_NAME=COPRRA
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=coprra_production
DB_USERNAME=coprra_user
DB_PASSWORD=strong_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Sentry Configuration
SENTRY_LARAVEL_DSN=your_sentry_dsn_here
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.1

# Telescope Configuration
TELESCOPE_ENABLED=false

# Debugbar Configuration
DEBUGBAR_ENABLED=false

# Clockwork Configuration
CLOCKWORK_ENABLED=false
```

### 5. إعداد Laravel

```bash
# توليد مفتاح التطبيق
php artisan key:generate

# تشغيل المايجريشن
php artisan migrate --force

# تشغيل السيدرز
php artisan db:seed

# تحسين الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 6. إعداد Apache

#### ملف .htaccess

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### إعدادات Apache

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/coprra/public
    
    <Directory /path/to/coprra/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/coprra_error.log
    CustomLog ${APACHE_LOG_DIR}/coprra_access.log combined
</VirtualHost>
```

### 7. إعداد SSL

```bash
# تثبيت Let's Encrypt
sudo apt install certbot python3-certbot-apache

# الحصول على شهادة SSL
sudo certbot --apache -d yourdomain.com
```

### 8. إعداد Cron Jobs

```bash
# إضافة المهام المجدولة
* * * * * cd /path/to/coprra && php artisan schedule:run >> /dev/null 2>&1
```

### 9. إعداد النسخ الاحتياطي

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/coprra"
PROJECT_DIR="/path/to/coprra"

# إنشاء مجلد النسخ الاحتياطي
mkdir -p $BACKUP_DIR

# نسخ احتياطي لقاعدة البيانات
mysqldump -u coprra_user -p coprra_production > $BACKUP_DIR/database_$DATE.sql

# نسخ احتياطي للملفات
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $PROJECT_DIR .

# حذف النسخ القديمة (أكثر من 7 أيام)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

## 🔍 اختبار النشر

### 1. اختبار الوظائف الأساسية

```bash
# اختبار الاتصال بقاعدة البيانات
php artisan tinker
>>> DB::connection()->getPdo();

# اختبار التطبيق
curl -I https://yourdomain.com
```

### 2. اختبار الأداء

```bash
# اختبار سرعة التحميل
curl -w "@curl-format.txt" -o /dev/null -s https://yourdomain.com

# اختبار الذاكرة
php artisan about
```

### 3. اختبار الأمان

```bash
# فحص الثغرات الأمنية
composer audit

# فحص الأمان
php artisan enlightn:security-check
```

## 📊 مراقبة الأداء

### 1. إعداد Sentry

```bash
# تثبيت Sentry
composer require sentry/sentry-laravel

# نشر التكوين
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

### 2. إعداد Log Files

```bash
# مراقبة السجلات
tail -f storage/logs/laravel.log

# مراقبة أخطاء Apache
tail -f /var/log/apache2/error.log
```

## 🚨 استكشاف الأخطاء

### مشاكل شائعة وحلولها

#### 1. خطأ 500 - Internal Server Error

```bash
# فحص السجلات
tail -f storage/logs/laravel.log

# فحص الصلاحيات
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 2. خطأ قاعدة البيانات

```bash
# فحص الاتصال
php artisan tinker
>>> DB::connection()->getPdo();

# إعادة تشغيل المايجريشن
php artisan migrate:fresh --seed
```

#### 3. مشاكل الذاكرة

```bash
# زيادة ذاكرة PHP
echo "memory_limit = 256M" >> /etc/php/8.2/apache2/php.ini

# إعادة تشغيل Apache
sudo systemctl restart apache2
```

## 📈 تحسين الأداء

### 1. تحسين قاعدة البيانات

```sql
-- إضافة فهارس
ALTER TABLE products ADD INDEX idx_name (name);
ALTER TABLE price_offers ADD INDEX idx_product_id (product_id);
ALTER TABLE price_offers ADD INDEX idx_store_id (store_id);
```

### 2. تحسين التخزين المؤقت

```bash
# تفعيل التخزين المؤقت
php artisan config:cache
php artisan route:cache
php artisan view:cache

# إعداد Redis للتخزين المؤقت
# في .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### 3. تحسين الصور

```bash
# تثبيت ImageMagick
sudo apt install imagemagick

# تحسين الصور
php artisan storage:link
```

## 🔐 الأمان

### 1. إعدادات الأمان

```bash
# حماية ملفات حساسة
echo "Deny from all" > .env
echo "Deny from all" > composer.json
echo "Deny from all" > package.json
```

### 2. تحديثات الأمان

```bash
# تحديث التبعيات
composer update

# فحص الثغرات
composer audit
```

## 📞 الدعم

### في حالة وجود مشاكل

1. **فحص السجلات**: `storage/logs/laravel.log`
2. **فحص الأخطاء**: `/var/log/apache2/error.log`
3. **فحص الأداء**: `php artisan about`
4. **فحص قاعدة البيانات**: `php artisan tinker`

### معلومات مفيدة

- **إصدار PHP**: `php -v`
- **إصدار Composer**: `composer -v`
- **إصدار Laravel**: `php artisan --version`
- **حالة التطبيق**: `php artisan about`

---

**تم إعداد المشروع بنجاح للنشر على Hostinger!** 🎉
