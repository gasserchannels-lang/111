# إعدادات DNS - Hostinger

## خوادم الأسماء
- **ns1.dns-parking.com**
- **ns2.dns-parking.com**

## سجلات DNS الحالية

### A Records:
```
Type: A
Name: @
Value: coprra.com.cdn.hstgr.net
TTL: 300

Type: A
Name: ftp
Value: 45.87.81.218
TTL: 1800
```

### CNAME Records:
```
Type: CNAME
Name: www
Value: www.coprra.com.cdn.hstgr.net
TTL: 300

Type: CNAME
Name: autodiscover
Value: autodiscover.mail.hostinger.com
TTL: 300

Type: CNAME
Name: autoconfig
Value: autoconfig.mail.hostinger.com
TTL: 300
```

### DKIM Records:
```
Type: CNAME
Name: hostingermail-a._domainkey
Value: hostingermail-a.dkim.mail.hostinger.com
TTL: 300

Type: CNAME
Name: hostingermail-b._domainkey
Value: hostingermail-b.dkim.mail.hostinger.com
TTL: 300

Type: CNAME
Name: hostingermail-c._domainkey
Value: hostingermail-c.dkim.mail.hostinger.com
TTL: 300
```

### MX Records:
```
Type: MX
Name: @
Value: mx1.hostinger.com
Priority: 5
TTL: 14400

Type: MX
Name: @
Value: mx2.hostinger.com
Priority: 10
TTL: 14400
```

### TXT Records:
```
Type: TXT
Name: @
Value: "v=spf1 include:_spf.mail.hostinger.com ~all"
TTL: 3600

Type: TXT
Name: _dmarc
Value: "v=DMARC1; p=none"
TTL: 3600
```

## إعدادات CDN

### الحالة الحالية:
- **CDN مفعل**: نعم
- **تاريخ التفعيل**: 2025-08-22
- **الرابط**: `coprra.com.cdn.hstgr.net`

### الفوائد:
- تحسين سرعة التحميل
- تقليل استهلاك الخادم
- تحسين الأداء العام

## سجل التغييرات

- **2025-08-22**: Hosting CDN enabled
- **2025-08-20**: Hosting CDN disabled/enabled
- **2025-08-13**: Hostinger mail activated

## ملاحظات مهمة

1. **CDN**: مفعل حالياً ويحسن الأداء
2. **البريد الإلكتروني**: مُعد بشكل صحيح مع DKIM
3. **SSL**: CAA records مُعدة لشهادات SSL
4. **الأمان**: SPF و DMARC مُعدة لحماية البريد

## اختبار DNS

### 1. اختبار A Record:
```bash
nslookup coprra.com
```

### 2. اختبار MX Records:
```bash
nslookup -type=MX coprra.com
```

### 3. اختبار SPF:
```bash
nslookup -type=TXT coprra.com
```

### 4. اختبار DKIM:
```bash
nslookup -type=TXT hostingermail-a._domainkey.coprra.com
```
