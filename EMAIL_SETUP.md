# إعدادات البريد الإلكتروني - Hostinger

## معلومات البريد الإلكتروني
- **الدومين**: `coprra.com`
- **انتهاء الصلاحية**: `2026-08-13`
- **خطة البريد**: `Business Starter Free Trial`
- **صندوق البريد**: `contact@coprra.com`

## إعدادات DNS المطلوبة

### 1. MX Records (لتلقي البريد الإلكتروني)
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

### 2. SPF Records (حماية السمعة)
```
Type: TXT
Name: @
Value: "v=spf1 include:_spf.hostinger.com ~all"
```

### 3. DKIM Records (مصادقة الرسالة)
```
Type: TXT
Name: default._domainkey
Value: "v=DKIM1; k=rsa; p=YOUR_DKIM_PUBLIC_KEY"
```

### 4. DMARC Records (تحسين التسليم)
```
Type: TXT
Name: _dmarc
Value: "v=DMARC1; p=quarantine; rua=mailto:dmarc@coprra.com"
```

## إعدادات Laravel

### ملف `.env`
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=contact@coprra.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="contact@coprra.com"
MAIL_FROM_NAME="Coprra"
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

## خطوات الإعداد في Hostinger

1. **إعداد DNS Records**:
   - انتقل إلى إعدادات DNS في Hostinger
   - أضف MX Records المذكورة أعلاه
   - أضف SPF, DKIM, DMARC Records

2. **إعداد صندوق البريد**:
   - تأكد من إنشاء `contact@coprra.com`
   - قم بتعيين كلمة مرور قوية

3. **اختبار الإعدادات**:
   - أرسل بريد إلكتروني تجريبي
   - تأكد من وصول البريد

## نصائح مهمة

1. **الأمان**: استخدم كلمة مرور قوية لصندوق البريد
2. **التسليم**: تأكد من إعداد SPF و DKIM و DMARC
3. **المراقبة**: راقب سجلات البريد الإلكتروني
4. **النسخ الاحتياطي**: احتفظ بنسخة احتياطية من إعدادات البريد
