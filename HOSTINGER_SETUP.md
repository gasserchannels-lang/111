# إعدادات Hostinger للمشروع

## معلومات الخادم
- **عنوان IP**: `45.87.81.218`
- **اسم الخادم**: `server480`
- **الموقع**: `Europe (Netherlands)`
- **مساحة القرص**: `50GB`
- **ذاكرة الوصول العشوائي**: `1536MB`
- **نوى المعالج**: `2`

## معلومات قاعدة البيانات
- **اسم قاعدة البيانات**: `u990109832_coprra_db`
- **اسم المستخدم**: `u990109832_gasser`
- **كلمة المرور**: `Hamo1510@Rayan146`
- **الخادم**: `localhost`
- **المنفذ**: `3306`

## معلومات FTP
- **IP FTP**: `ftp://45.87.81.218`
- **مضيف FTP**: `ftp://coprra.com`
- **مستخدم FTP**: `u990109832`
- **مسار التحميل**: `public_html`

## تفاصيل SSH
- **IP**: `45.87.81.218`
- **المنفذ**: `65002`
- **اسم المستخدم**: `u990109832`
- **الحالة**: `ACTIVE`
- **أمر الاتصال**: `ssh -p 65002 u990109832@45.87.81.218`

## Git Integration
- **مفتاح SSH**: متاح للنشر من GitHub/Bitbucket
- **النشر التلقائي**: مدعوم
- **المستودعات العامة**: مدعومة
- **المستودعات الخاصة**: مدعومة

## CDN (Content Delivery Network)
- **الحالة**: مفعل ✅
- **الرابط**: `https://coprra.com.cdn.hstgr.net`
- **النوع**: Hostinger CDN
- **الوظيفة**: تسريع تحميل الملفات الثابتة

## شهادة SSL
- **النوع**: `Lifetime SSL (Google)`
- **الحالة**: `نشط` ✅
- **تم الإنشاء**: `2025-07-28`
- **تنتهي الصلاحية**: `أبداً` (مدى الحياة)
- **الحماية**: ماسح البرمجيات الخبيثة متوفر

## خوادم الأسماء
- **ns1.dns-parking.com**
- **ns2.dns-parking.com**

## سجلات DNS الحالية

### A Records:
- **@** → `coprra.com.cdn.hstgr.net` (TTL: 300)
- **ftp** → `45.87.81.218` (TTL: 1800)

### CNAME Records:
- **www** → `www.coprra.com.cdn.hstgr.net` (TTL: 300)
- **autodiscover** → `autodiscover.mail.hostinger.com` (TTL: 300)
- **autoconfig** → `autoconfig.mail.hostinger.com` (TTL: 300)
- **DKIM Records**:
  - `hostingermail-a._domainkey` → `hostingermail-a.dkim.mail.hostinger.com`
  - `hostingermail-b._domainkey` → `hostingermail-b.dkim.mail.hostinger.com`
  - `hostingermail-c._domainkey` → `hostingermail-c.dkim.mail.hostinger.com`

### MX Records:
- **mx1.hostinger.com** (Priority: 5, TTL: 14400)
- **mx2.hostinger.com** (Priority: 10, TTL: 14400)

### TXT Records:
- **SPF**: `"v=spf1 include:_spf.mail.hostinger.com ~all"` (TTL: 3600)
- **DMARC**: `"v=DMARC1; p=none"` (TTL: 3600)

### CAA Records:
- متعددة لشهادات SSL من مختلف المزودين

## إعدادات CDN
- **الحالة الحالية**: Hosting CDN enabled
- **تاريخ التفعيل**: 2025-08-22
- **الرابط**: `coprra.com.cdn.hstgr.net`

## سجل التغييرات
- **2025-08-22**: Hosting CDN enabled
- **2025-08-20**: Hosting CDN disabled/enabled
- **2025-08-13**: Hostinger mail activated

## إعدادات البريد الإلكتروني
- **الدومين**: `coprra.com`
- **انتهاء الصلاحية**: `2026-08-13`
- **خطة البريد**: `Business Starter Free Trial`
- **صندوق البريد**: `contact@coprra.com`

### إعدادات DNS المطلوبة:
- **MX Records**: لتلقي رسائل البريد الإلكتروني
- **SPF Records**: لحماية سمعة البريد الإلكتروني
- **DKIM Records**: لمصادقة الرسالة
- **DMARC Records**: لزيادة إمكانية التسليم

## الملفات المطلوب تحديثها في الاستضافة

### 1. ملف `.env`
```env
APP_NAME="Coprra"
APP_ENV=production
APP_KEY=base64:mAkbpuXF7OVTRIDCIMkD8+xw6xVi7pge9CFImeqZaxE=
APP_DEBUG=false
APP_URL=https://coprra.com
CDN_URL=https://coprra.com.cdn.hstgr.net

# SSL Configuration
FORCE_HTTPS=true
SECURE_HEADERS=true
HSTS_ENABLED=true

# PHP Configuration (Hostinger optimized)
PHP_MEMORY_LIMIT=2048M
PHP_MAX_EXECUTION_TIME=360
PHP_UPLOAD_MAX_FILESIZE=2048M
PHP_POST_MAX_SIZE=2048M

# SSH Configuration
SSH_HOST=45.87.81.218
SSH_PORT=65002
SSH_USERNAME=u990109832
SSH_PATH=/home/u990109832/public_html

# Deployment Configuration
BACKUP_ENABLED=true
BACKUP_PATH=/home/u990109832/backups

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration for Hostinger
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u990109832_coprra_db
DB_USERNAME=u990109832_gasser
DB_PASSWORD=Hamo1510@Rayan146

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=1440
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=none

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mail.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=contact@coprra.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="contact@coprra.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

## الأوامر المطلوب تنفيذها في الاستضافة

```bash
# 1. تثبيت التبعيات
composer install --optimize-autoloader --no-dev

# 2. إنشاء قاعدة البيانات للاختبارات
mysql -u u990109832_gasser -p -e "CREATE DATABASE IF NOT EXISTS u990109832_coprra_db_test;"

# 3. تشغيل المايجريشن
php artisan migrate --force

# 4. تشغيل السيدر
php artisan db:seed --force

# 5. تحسين التطبيق
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. إنشاء رابط التخزين
php artisan storage:link

# 7. تحسين الأداء للخادم
php artisan optimize
php artisan queue:restart
```

## إعدادات PHP المطلوبة

### إصدار PHP الحالي:
- **PHP 8.2.28** ✅ (مثالي للاستقرار والأداء)
- **الموديلات المتاحة**: 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4
- **OPcache**: مفعل ومُحسن ✅

### الملحقات المثبتة:
- **قاعدة البيانات**: `mysqlnd`, `nd_mysqli`, `pdo_mysql`, `pdo_sqlite`
- **الأمان**: `openssl`, `sodium`, `hash`
- **الأداء**: `opcache`, `apcu`, `memcache`
- **الملفات**: `fileinfo`, `zip`, `gd`, `imagick`
- **JSON/XML**: `json`, `xml`, `simplexml`, `dom`

### إعدادات PHP الحالية في Hostinger:
```ini
# إعدادات الذاكرة والمعالج
memory_limit = 2048M
max_execution_time = 360
max_input_time = 360
max_input_vars = 5000

# إعدادات التحميل
upload_max_filesize = 2048M
post_max_size = 2048M
max_file_uploads = 20

# إعدادات OPcache (مفعل ومُحسن)
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256M
opcache.max_accelerated_files = 16229
opcache.max_file_size = 262144
opcache.interned_strings_buffer = 16
opcache.jit = tracing
opcache.optimization_level = 0x7FFEBFFF

# إعدادات الجلسات
session.gc_maxlifetime = 1440
session.cookie_lifetime = 0
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_httponly = 1
session.cookie_samesite = None
session.save_path = /opt/alt/php82/var/lib/php/session

# إعدادات الأمان
expose_php = Off
allow_url_fopen = On
allow_url_include = Off
disable_functions = system,exec,shell_exec,passthru,mysql_list_dbs,ini_alter,dl,symlink,link,chgrp,leak,popen,apache_child_terminate,virtual,mb_send_mail

# إعدادات المنطقة الزمنية
date.timezone = UTC

# إعدادات الضغط
zlib.output_compression = 1

# إعدادات الخطأ
display_errors = Off
log_errors = On
error_reporting = E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_RECOVERABLE_ERROR | E_USER_DEPRECATED
```

## إعدادات Nginx المطلوبة

### ملف `nginx.conf`
```nginx
server {
    listen 80;
    listen 443 ssl;
    server_name coprra.com www.coprra.com;
    root /home/u990109832/public_html/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Laravel Configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static Files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security
    location ~ /\. {
        deny all;
    }
}
```

## ملاحظات مهمة

1. **قاعدة البيانات**: تم تحديث الإعدادات لتستخدم MySQL بدلاً من SQLite
2. **الاختبارات**: تحتاج قاعدة بيانات منفصلة للاختبارات
3. **الأمان**: تأكد من حماية ملف `.env` من الوصول العام
4. **الأداء**: تم تحسين إعدادات التطبيق للإنتاج

## إعدادات الأداء المطلوبة

### بناءً على مواصفات الخادم:
- **ذاكرة الوصول العشوائي**: 1536MB
- **نوى المعالج**: 2
- **مساحة القرص**: 50GB

### إعدادات CDN:
- **CDN مفعل**: نعم
- **الرابط**: `coprra.com.cdn.hstgr.net`
- **الفوائد**: تحسين سرعة التحميل، تقليل استهلاك الخادم

### إعدادات Laravel المطلوبة:
```php
// config/app.php
'debug' => false,
'log_level' => 'error',

// config/cache.php
'default' => 'file',

// config/session.php
'lifetime' => 120,
'expire_on_close' => false,
```

## المشاكل المحتملة

1. **MySQL Extension**: تأكد من تفعيل `pdo_mysql` في PHP
2. **الصلاحيات**: تأكد من صلاحيات قاعدة البيانات
3. **الذاكرة**: قد تحتاج زيادة `memory_limit` في PHP
4. **FTP**: تأكد من رفع الملفات إلى `public_html` وليس `public_html/public`
5. **SSL**: تأكد من تفعيل SSL للدومين

## خطوات النشر

1. **رفع الملفات عبر FTP**:
   - رفع جميع الملفات إلى `public_html`
   - نقل محتويات `public/` إلى `public_html/`

2. **إعداد قاعدة البيانات**:
   - إنشاء قاعدة البيانات `u990109832_coprra_db`
   - تشغيل المايجريشن

3. **إعداد ملف `.env`**:
   - نسخ الإعدادات المذكورة أعلاه
   - تحديث `APP_URL` إلى `https://coprra.com`

4. **اختبار الموقع**:
   - زيارة `https://coprra.com`
   - اختبار جميع الوظائف

## إعدادات DNS المطلوبة

### MX Records (لتلقي البريد الإلكتروني):
```
Type: MX
Name: @
Value: mx1.hostinger.com
Priority: 10

Type: MX
Name: @
Value: mx2.hostinger.com
Priority: 20
```

### SPF Records (حماية السمعة):
```
Type: TXT
Name: @
Value: "v=spf1 include:_spf.hostinger.com ~all"
```

### DKIM Records (مصادقة الرسالة):
```
Type: TXT
Name: default._domainkey
Value: "v=DKIM1; k=rsa; p=YOUR_DKIM_PUBLIC_KEY"
```

### DMARC Records (تحسين التسليم):
```
Type: TXT
Name: _dmarc
Value: "v=DMARC1; p=quarantine; rua=mailto:dmarc@coprra.com"
```

## إعدادات البريد الإلكتروني في Laravel

### ملف `config/mail.php`:
```php
'default' => env('MAIL_MAILER', 'smtp'),

'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'host' => env('MAIL_HOST', 'mail.hostinger.com'),
        'port' => env('MAIL_PORT', 587),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'timeout' => null,
        'local_domain' => env('MAIL_EHLO_DOMAIN'),
    ],
],

'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'contact@coprra.com'),
    'name' => env('MAIL_FROM_NAME', 'Coprra'),
],
```

## اختبار البريد الإلكتروني

### 1. اختبار الإعدادات:
```bash
php artisan tinker
Mail::raw('Test email', function ($message) {
    $message->to('contact@coprra.com')->subject('Test Email');
});
```

### 2. اختبار إرسال البريد:
```bash
php artisan mail:test contact@coprra.com
```

## قائمة التحقق النهائية

### ✅ ما تم إنجازه:
- [x] **PHP 8.2.28** (مفعل ومثالي)
- [x] **قاعدة البيانات** (مُعدة ومُحسنة)
- [x] **البريد الإلكتروني** (مُعد ومُحسن)
- [x] **SSH** (مفعل ومُعد)
- [x] **Git Integration** (جاهز للنشر التلقائي)
- [x] **CDN** (مفعل ومُحسن) ✅

### ⚠️ ما يحتاج إلى إعداد:
- [x] **OPcache** (مفعل ومُحسن) ✅
- [ ] **Cron Jobs** (Laravel Scheduler) - دليل جاهز
- [x] **SSL Certificate** (Lifetime SSL مفعل) ✅
- [ ] **File Permissions** (صلاحيات الملفات) - سكريبت جاهز
- [ ] **GitHub Actions** (النشر التلقائي) - دليل جاهز

## نصائح مهمة

1. **الأداء**: استخدم OPcache و CDN لتحسين الأداء
2. **الأمان**: تأكد من تحديث كلمات المرور بانتظام
3. **النسخ الاحتياطية**: قم بإنشاء نسخ احتياطية منتظمة
4. **المراقبة**: راقب سجلات الأخطاء بانتظام
