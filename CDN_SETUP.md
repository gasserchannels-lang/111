# إعداد CDN - Hostinger

## معلومات CDN

### الحالة الحالية:
- **مفعل**: ✅
- **الرابط**: `https://coprra.com.cdn.hstgr.net`
- **النوع**: Hostinger CDN
- **الوظيفة**: تسريع تحميل الملفات الثابتة

## الملفات المدعومة

### 1. **الصور**:
- `.jpg`, `.jpeg`, `.png`, `.gif`, `.webp`, `.svg`
- **المسار**: `https://coprra.com.cdn.hstgr.net/images/`

### 2. **الملفات الثابتة**:
- `.css`, `.js`, `.woff`, `.woff2`, `.ttf`
- **المسار**: `https://coprra.com.cdn.hstgr.net/assets/`

### 3. **الملفات العامة**:
- `.pdf`, `.zip`, `.doc`, `.docx`
- **المسار**: `https://coprra.com.cdn.hstgr.net/files/`

## إعداد Laravel للCDN

### 1. ملف `.env`:
```env
CDN_URL=https://coprra.com.cdn.hstgr.net
```

### 2. ملف `config/app.php`:
```php
'cdn_url' => env('CDN_URL', 'https://coprra.com.cdn.hstgr.net'),
```

### 3. استخدام CDN في Blade:
```php
<!-- الصور -->
<img src="{{ config('app.cdn_url') }}/images/logo.png" alt="Logo">

<!-- CSS -->
<link href="{{ config('app.cdn_url') }}/assets/css/app.css" rel="stylesheet">

<!-- JavaScript -->
<script src="{{ config('app.cdn_url') }}/assets/js/app.js"></script>
```

### 4. استخدام CDN في Vite:
```javascript
// vite.config.js
export default defineConfig({
    plugins: [laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
    })],
    build: {
        rollupOptions: {
            output: {
                assetFileNames: 'assets/[name].[hash][extname]',
                chunkFileNames: 'assets/[name].[hash].js',
                entryFileNames: 'assets/[name].[hash].js',
            }
        }
    }
});
```

## اختبار CDN

### 1. اختبار الصور:
```bash
curl -I https://coprra.com.cdn.hstgr.net/images/logo.png
```

### 2. اختبار CSS:
```bash
curl -I https://coprra.com.cdn.hstgr.net/assets/css/app.css
```

### 3. اختبار JavaScript:
```bash
curl -I https://coprra.com.cdn.hstgr.net/assets/js/app.js
```

## تحسين الأداء

### 1. **ضغط الملفات**:
```bash
# ضغط CSS
gzip -9 assets/css/app.css

# ضغط JavaScript
gzip -9 assets/js/app.js
```

### 2. **إعداد Headers**:
```apache
# .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>
```

### 3. **إعداد Cache Control**:
```apache
# .htaccess
<IfModule mod_headers.c>
    <FilesMatch "\.(css|js|png|jpg|jpeg|gif|webp|woff|woff2)$">
        Header set Cache-Control "max-age=31536000, public"
    </FilesMatch>
</IfModule>
```

## مراقبة CDN

### 1. **اختبار السرعة**:
- استخدم GTmetrix
- استخدم PageSpeed Insights
- استخدم WebPageTest

### 2. **مراقبة الاستخدام**:
- تحقق من لوحة تحكم Hostinger
- راقب إحصائيات CDN
- تحقق من الأخطاء

## استكشاف الأخطاء

### 1. **مشاكل التحميل**:
```bash
# اختبار الاتصال
ping coprra.com.cdn.hstgr.net

# اختبار DNS
nslookup coprra.com.cdn.hstgr.net
```

### 2. **مشاكل الملفات**:
```bash
# تحقق من وجود الملف
curl -I https://coprra.com.cdn.hstgr.net/assets/css/app.css

# تحقق من الصلاحيات
ls -la public/assets/css/app.css
```

### 3. **مشاكل Cache**:
- امسح cache المتصفح
- امسح cache CDN
- تحقق من إعدادات Cache

## نصائح مهمة

1. **استخدم أسماء ملفات فريدة**: لتجنب مشاكل Cache
2. **ضغط الملفات**: لتحسين الأداء
3. **إعداد Headers**: لتحسين Cache
4. **مراقبة الأداء**: بانتظام
5. **اختبار CDN**: بعد كل تحديث

## الخلاصة

CDN مفعل ومُحسن في Hostinger، مما يوفر:
- تسريع تحميل الملفات الثابتة
- تقليل الحمل على الخادم
- تحسين تجربة المستخدم
- تحسين SEO
