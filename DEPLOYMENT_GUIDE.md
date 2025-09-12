# دليل النشر - Hostinger

## طرق النشر المتاحة

### 1. النشر عبر SSH (الأفضل)

#### الاتصال بالخادم:
```bash
ssh -p 65002 u990109832@45.87.81.218
```

#### خطوات النشر:
```bash
# 1. الانتقال إلى مجلد الموقع
cd public_html

# 2. إنشاء نسخة احتياطية
cp -r . ../backup_$(date +%Y%m%d_%H%M%S)

# 3. حذف الملفات القديمة (احتفظ بـ .htaccess)
rm -rf * .[^.]*

# 4. تحميل الملفات الجديدة
# (استخدم SCP أو SFTP)

# 5. تعيين الصلاحيات
chmod -R 755 .
chmod 644 .htaccess

# 6. تشغيل أوامر Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 2. النشر عبر Git (تلقائي)

#### إعداد المستودع:
1. اذهب إلى "GIT" في لوحة تحكم Hostinger
2. أضف مفتاح SSH إلى GitHub:
   ```
   ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQDfSq/Tyja1GlG8rY9/yzGJPGMvyFatrAGScjMDgHwFPGANjVPsO0ncy6WWCnnSHmAPiVuBEUKPJ0+4nFygqn5DyzLxYU81YZaYM518Z5gLfuXhLgDLRFOK6wYQR54+JUB5mkYBlHnAMAVpalOnzzvaDytdKb/xVbWKuOKBbA/jgEaRvjNHa27DiDnSW5XXm1L51ZPTa1PV9H83D2FXrtM+36KPVChWyigVejIEXdK9TV9AyPeZlx8X+FZCY6f3e+nUnb6luLCQIpS42XiqmKm0zpOdH0UE9LOveMsEV0GHSHepp5VykB4t08Yi40sh/Fv5xCJjMAwWQxa+s2VKmegqmUh3f6bxbN0x9WveCSeKMWCLaL4xezrKvp5kSkuIRY6MvvG+UAmxgAJJ8Nz6cM5qWv3tfxqeTHBtPR9cz0g5jG8o9AXzM2UrajqMo/WixkodbmGJPMaop5XHHyqX9d7/2heUulVu6IdM+hSeI/N8if7fHOh7As4GJfCs4QhHvBU= u990109832@nl-srv-web480.main-hosting.eu
   ```

#### إعداد النشر التلقائي:
1. **المستودع**: `https://github.com/username/coprra.git`
2. **الفرع**: `main`
3. **المجلد**: `public_html`

### 3. النشر عبر FTP

#### استخدام FileZilla أو WinSCP:
- **الخادم**: `45.87.81.218`
- **المنفذ**: `21`
- **المستخدم**: `u990109832`
- **المجلد**: `public_html`

## خطوات ما بعد النشر

### 1. إعداد قاعدة البيانات:
```bash
# الاتصال بقاعدة البيانات
mysql -u u990109832_gasser -p u990109832_coprra_db

# تشغيل migrations
php artisan migrate --force

# تشغيل seeders
php artisan db:seed --force
```

### 2. تحسين الأداء:
```bash
# تنظيف التخزين المؤقت
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# إنشاء التخزين المؤقت
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. إعداد Cron Jobs:
```bash
# في لوحة تحكم Hostinger > Cron Jobs
* * * * * cd /home/u990109832/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### 4. إعداد SSL:
- SSL مجاني متاح في Hostinger
- تفعيل "Force HTTPS" في لوحة التحكم

## اختبار النشر

### 1. اختبار الموقع:
```bash
# اختبار الصفحة الرئيسية
curl -I https://coprra.com

# اختبار API
curl -I https://coprra.com/api/health
```

### 2. اختبار قاعدة البيانات:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### 3. اختبار البريد الإلكتروني:
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

## استكشاف الأخطاء

### 1. مشاكل الصلاحيات:
```bash
# تعيين صلاحيات الملفات
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 storage bootstrap/cache
```

### 2. مشاكل قاعدة البيانات:
```bash
# اختبار الاتصال
php artisan tinker
>>> DB::connection()->getPdo();
```

### 3. مشاكل التخزين المؤقت:
```bash
# تنظيف التخزين المؤقت
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## نصائح مهمة

1. **النسخ الاحتياطية**: قم بإنشاء نسخة احتياطية قبل كل نشر
2. **الاختبار**: اختبر الموقع محلياً قبل النشر
3. **المراقبة**: راقب سجلات الأخطاء بعد النشر
4. **الأمان**: تأكد من تحديث كلمات المرور بانتظام
5. **الأداء**: استخدم OPcache و CDN لتحسين الأداء
