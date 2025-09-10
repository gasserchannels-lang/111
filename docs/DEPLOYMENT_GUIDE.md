# دليل النشر - COPRRA

## متطلبات النشر

### 1. متطلبات الخادم
- **PHP**: 8.2 أو أحدث
- **MySQL**: 8.0 أو أحدث
- **Redis**: 6.0 أو أحدث
- **Composer**: 2.0 أو أحدث
- **Node.js**: 18.0 أو أحدث
- **NPM**: 8.0 أو أحدث

### 2. إعدادات البيئة (.env)

```bash
# إعدادات التطبيق
APP_NAME=COPRRA
APP_ENV=production
APP_KEY=base64:mAkbpuXF7OVTRIDCIMkD8+xw6xVi7pge9CFImeqZaxE=
APP_DEBUG=false
APP_URL=https://your-domain.com

# إعدادات قاعدة البيانات
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coprra
DB_USERNAME=your_username
DB_PASSWORD=your_password

# إعدادات البريد الإلكتروني
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# إعدادات Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# إعدادات COPRRA
COPRRA_DEFAULT_CURRENCY=USD
COPRRA_DEFAULT_CONDITION=new
COPRRA_MAX_PRICE_RANGE=10000
COPRRA_MIN_PRICE_RANGE=1
DEFAULT_ITEMS_PER_PAGE=20
MAX_WISHLIST_ITEMS=100
MAX_PRICE_ALERTS=50

# أسعار الصرف
EXCHANGE_RATE_USD=1.0
EXCHANGE_RATE_EUR=0.85
EXCHANGE_RATE_GBP=0.73
EXCHANGE_RATE_JPY=110.0
EXCHANGE_RATE_SAR=3.75
EXCHANGE_RATE_AED=3.67
EXCHANGE_RATE_EGP=30.9
```

## خطوات النشر

### 1. تحضير الخادم
```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت PHP 8.2
sudo apt install php8.2-fpm php8.2-mysql php8.2-redis php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-intl

# تثبيت Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# تثبيت Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# تثبيت MySQL
sudo apt install mysql-server

# تثبيت Redis
sudo apt install redis-server
```

### 2. إعداد قاعدة البيانات
```sql
CREATE DATABASE coprra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'coprra_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON coprra.* TO 'coprra_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. نشر التطبيق
```bash
# نسخ الملفات
git clone https://github.com/your-repo/coprra.git
cd coprra

# تثبيت التبعيات
composer install --optimize-autoloader --no-dev
npm install --production

# إعداد البيئة
cp .env.example .env
nano .env  # تعديل الإعدادات

# إنتاج المفتاح
php artisan key:generate

# تشغيل المايجريشن
php artisan migrate --force

# تشغيل السيدر
php artisan db:seed --force

# بناء الأصول
npm run build

# تحسين التطبيق
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# ربط التخزين
php artisan storage:link
```

### 4. إعداد Nginx
```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name your-domain.com;
    root /path/to/coprra/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';";
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=(), interest-cohort=()";
    add_header X-Permitted-Cross-Domain-Policies "none";
    add_header Cross-Origin-Embedder-Policy "require-corp";
    add_header Cross-Origin-Opener-Policy "same-origin";
    add_header Cross-Origin-Resource-Policy "same-origin";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
}
```

### 5. إعداد Supervisor للـ Queue
```ini
[program:coprra-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/coprra/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/coprra/storage/logs/worker.log
stopwaitsecs=3600
```

### 6. إعداد Cron Jobs
```bash
# إضافة إلى crontab
* * * * * cd /path/to/coprra && php artisan schedule:run >> /dev/null 2>&1
```

### 7. فحص جاهزية النشر
```bash
php artisan deployment:check
```

## نصائح الأمان

1. **تأكد من تحديث جميع التبعيات**
2. **استخدم HTTPS دائماً**
3. **قم بتشفير البيانات الحساسة**
4. **اضبط صلاحيات الملفات بشكل صحيح**
5. **راقب السجلات بانتظام**
6. **قم بعمل نسخ احتياطية دورية**

## استكشاف الأخطاء

### مشاكل شائعة:
- **خطأ 500**: تحقق من السجلات في `storage/logs/`
- **خطأ قاعدة البيانات**: تأكد من إعدادات `.env`
- **مشاكل التخزين**: تأكد من `php artisan storage:link`
- **مشاكل الذاكرة**: زد `memory_limit` في `php.ini`

### أوامر مفيدة:
```bash
# مسح الكاش
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# إعادة تحسين
php artisan optimize

# فحص الصحة
php artisan health:check
```
