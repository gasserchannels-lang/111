# إعداد Cron Jobs - Hostinger

## Cron Jobs المطلوبة

### 1. Laravel Scheduler (الأهم):
```bash
* * * * * cd /home/u990109832/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### 2. تنظيف التخزين المؤقت (اختياري):
```bash
0 2 * * * cd /home/u990109832/public_html && php artisan cache:clear >> /dev/null 2>&1
```

### 3. تنظيف الملفات المؤقتة (اختياري):
```bash
0 3 * * * find /home/u990109832/public_html/storage/logs -name "*.log" -mtime +7 -delete
```

## خطوات الإعداد في Hostinger:

1. **انتقل إلى لوحة التحكم**:
   - اذهب إلى "متقدم" > "وظائف كرون"

2. **أضف Cron Job**:
   - **Command**: `cd /home/u990109832/public_html && php artisan schedule:run >> /dev/null 2>&1`
   - **Minute**: `*`
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`

3. **احفظ الإعدادات**

## اختبار Cron Jobs:

### 1. اختبار Laravel Scheduler:
```bash
# في SSH
cd /home/u990109832/public_html
php artisan schedule:list
php artisan schedule:run
```

### 2. اختبار Cron Job:
```bash
# في SSH
crontab -l
```

## نصائح مهمة:

1. **تأكد من المسار الصحيح**: `/home/u990109832/public_html`
2. **استخدم المسار الكامل**: `php` بدلاً من `php`
3. **أضف إعادة التوجيه**: `>> /dev/null 2>&1`
4. **اختبر Cron Job**: قبل تفعيله

## استكشاف الأخطاء:

### 1. مشاكل المسار:
```bash
# تحقق من المسار
pwd
ls -la
```

### 2. مشاكل الصلاحيات:
```bash
# تحقق من الصلاحيات
ls -la /home/u990109832/public_html
```

### 3. مشاكل PHP:
```bash
# تحقق من PHP
which php
php -v
```
