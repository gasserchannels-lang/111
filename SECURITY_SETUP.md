# إعدادات الأمان - Hostinger

## شهادة SSL

### التفاصيل الحالية:
- **النوع**: `Lifetime SSL (Google)`
- **الحالة**: `نشط` ✅
- **تم الإنشاء**: `2025-07-28`
- **تنتهي الصلاحية**: `أبداً` (مدى الحياة)

### المميزات:
- **مجانية**: مدى الحياة
- **تشفير قوي**: 256-bit encryption
- **دعم جميع المتصفحات**: متوافق مع جميع المتصفحات الحديثة
- **تحديث تلقائي**: يتم تحديثها تلقائياً

## الحماية المتاحة

### 1. ماسح البرمجيات الخبيثة:
- **متوفر**: ✅
- **الوظيفة**: فحص الملفات تلقائياً
- **التحديث**: تلقائي

### 2. حماية DDoS:
- **متوفر**: ✅
- **الوظيفة**: حماية من هجمات DDoS
- **المستوى**: أساسي

### 3. جدار الحماية:
- **متوفر**: ✅
- **الوظيفة**: فلترة الطلبات المشبوهة
- **التكوين**: تلقائي

## إعدادات Laravel للأمان

### 1. ملف `.env`:
```env
# SSL Configuration
APP_URL=https://coprra.com
FORCE_HTTPS=true

# Security Headers
SECURE_HEADERS=true
HSTS_ENABLED=true
```

### 2. ملف `config/app.php`:
```php
'url' => env('APP_URL', 'https://coprra.com'),
'asset_url' => env('ASSET_URL', 'https://coprra.com'),
```

### 3. ملف `config/session.php`:
```php
'secure' => env('SESSION_SECURE', true),
'http_only' => env('SESSION_HTTP_ONLY', true),
'same_site' => env('SESSION_SAME_SITE', 'none'),
```

## اختبار الأمان

### 1. اختبار SSL:
```bash
# اختبار شهادة SSL
curl -I https://coprra.com

# اختبار التشفير
openssl s_client -connect coprra.com:443
```

### 2. اختبار HTTPS:
```bash
# اختبار إعادة التوجيه
curl -I http://coprra.com

# يجب أن يعيد 301 أو 302 إلى HTTPS
```

### 3. اختبار Headers:
```bash
# اختبار Security Headers
curl -I https://coprra.com | grep -i "strict-transport-security\|x-frame-options\|x-content-type-options"
```

## إعدادات Nginx للأمان

### ملف `.htaccess`:
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Hide Server Information
ServerTokens Prod
ServerSignature Off
```

## مراقبة الأمان

### 1. سجلات الأمان:
```bash
# مراقبة سجلات الأمان
tail -f /var/log/security.log

# مراقبة سجلات Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### 2. فحص الملفات:
```bash
# فحص الملفات المشبوهة
find /home/u990109832/public_html -name "*.php" -exec grep -l "eval\|base64_decode\|system\|exec" {} \;

# فحص الصلاحيات
find /home/u990109832/public_html -type f -perm 777
```

## نصائح الأمان

### 1. تحديثات منتظمة:
- تحديث Laravel بانتظام
- تحديث المكتبات والتبعيات
- مراقبة إشعارات الأمان

### 2. كلمات المرور:
- استخدام كلمات مرور قوية
- تغيير كلمات المرور بانتظام
- تفعيل المصادقة الثنائية

### 3. النسخ الاحتياطية:
- إنشاء نسخ احتياطية منتظمة
- اختبار استعادة النسخ الاحتياطية
- تخزين النسخ في مكان آمن

### 4. المراقبة:
- مراقبة سجلات الأخطاء
- مراقبة حركة المرور
- مراقبة استخدام الموارد

## استكشاف الأخطاء

### 1. مشاكل SSL:
```bash
# اختبار شهادة SSL
openssl s_client -connect coprra.com:443 -servername coprra.com

# فحص صلاحية الشهادة
echo | openssl s_client -servername coprra.com -connect coprra.com:443 2>/dev/null | openssl x509 -noout -dates
```

### 2. مشاكل HTTPS:
```bash
# اختبار إعادة التوجيه
curl -v http://coprra.com

# اختبار Headers
curl -I https://coprra.com
```

### 3. مشاكل الأمان:
```bash
# فحص الملفات المشبوهة
grep -r "eval\|base64_decode\|system\|exec" /home/u990109832/public_html/

# فحص الصلاحيات
ls -la /home/u990109832/public_html/
```
