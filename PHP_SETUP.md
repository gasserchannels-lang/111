# إعدادات PHP - Hostinger

## إصدار PHP الحالي
- **PHP 8.2** ✅ (مثالي للاستقرار والأداء)
- **الموديلات المتاحة**: 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4
- **التوصية**: البقاء على PHP 8.2 (مستقر ومحسن)

## الملحقات المثبتة

### قاعدة البيانات:
- `mysqlnd` - MySQL Native Driver
- `nd_mysqli` - MySQL Improved Extension
- `pdo_mysql` - PDO MySQL Driver
- `pdo_sqlite` - PDO SQLite Driver

### الأمان:
- `openssl` - OpenSSL Support
- `sodium` - Sodium Encryption
- `hash` - Hash Functions

### الأداء:
- `opcache` - OPcache (مفعل)
- `apcu` - APCu User Cache
- `memcache` - Memcache Support

### الملفات:
- `fileinfo` - File Information
- `zip` - ZIP Archive Support
- `gd` - GD Graphics Library
- `imagick` - ImageMagick Support

### JSON/XML:
- `json` - JSON Support
- `xml` - XML Support
- `simplexml` - SimpleXML
- `dom` - DOM Support

## إعدادات PHP الحالية

### الذاكرة والمعالج:
```ini
memory_limit = 1536M
max_execution_time = 360
max_input_time = 360
max_input_vars = 5000
```

### التحميل:
```ini
upload_max_filesize = 1536M
post_max_size = 1536M
max_file_uploads = 20
```

### OPcache:
```ini
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256M
opcache.max_accelerated_files = 16229
opcache.max_file_size = 262144
opcache.interned_strings_buffer = 16
```

### الجلسات:
```ini
session.gc_maxlifetime = 1440
session.cookie_lifetime = 0
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_httponly = 1
session.cookie_samesite = None
session.save_path = /opt/alt/php82/var/lib/php/session
```

### الأمان:
```ini
expose_php = Off
allow_url_fopen = On
allow_url_include = Off
disable_functions = system,exec,shell_exec,passthru,mysql_list_dbs,ini_alter,dl,symlink,link,chgrp,leak,popen,apache_child_terminate,virtual,mb_send_mail
```

### المنطقة الزمنية:
```ini
date.timezone = UTC
```

### الضغط:
```ini
zlib.output_compression = 1
```

### الخطأ:
```ini
display_errors = Off
log_errors = On
error_reporting = E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_RECOVERABLE_ERROR | E_USER_DEPRECATED
```

## اختبار PHP

### 1. اختبار الإصدار:
```bash
php -v
```

### 2. اختبار الملحقات:
```bash
php -m
```

### 3. اختبار OPcache:
```bash
php -i | grep opcache
```

### 4. اختبار قاعدة البيانات:
```bash
php -r "echo extension_loaded('pdo_mysql') ? 'PDO MySQL: OK' : 'PDO MySQL: Missing';"
```

## نصائح مهمة

1. **الأداء**: OPcache مفعل ويحسن الأداء
2. **الأمان**: الوظائف الخطيرة معطلة
3. **الذاكرة**: 1536M كافية لمعظم التطبيقات
4. **التحميل**: 1536M كافية للملفات الكبيرة
5. **الجلسات**: إعدادات أمان محسنة

## تحديث PHP

### في Hostinger:
1. انتقل إلى "تكوين PHP"
2. اختر الإصدار المطلوب
3. اضغط "تحديث"
4. انتظر 1-2 دقيقة

### تحذير:
- سيتم إيقاف الموقع مؤقتاً
- تأكد من توافق التطبيق مع الإصدار الجديد
