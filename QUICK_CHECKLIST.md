# قائمة التحقق السريعة - Hostinger

## ⚡ **الخطوات السريعة** (18 دقيقة):

### 1. **Cron Jobs** (5 دقائق):
- [ ] اذهب إلى Hostinger > متقدم > وظائف كرون
- [ ] أضف: `* * * * * cd /home/u990109832/public_html && php artisan schedule:run >> /dev/null 2>&1`
- [ ] احفظ

### 2. **File Permissions** (3 دقائق):
- [ ] SSH: `ssh -p 65002 u990109832@45.87.81.218`
- [ ] `cd /home/u990109832/public_html`
- [ ] `chmod +x setup_hostinger.sh`
- [ ] `./setup_hostinger.sh`

### 3. **GitHub Actions** (10 دقائق):
- [ ] GitHub > Settings > Secrets > Actions
- [ ] أضف: `HOSTINGER_HOST=45.87.81.218`
- [ ] أضف: `HOSTINGER_PORT=65002`
- [ ] أضف: `HOSTINGER_USERNAME=u990109832`
- [ ] أضف: `HOSTINGER_SSH_KEY=[مفتاح SSH]`
- [ ] Hostinger > SSH > مفاتيح SSH > أضف المفتاح العام

## 🧪 **الاختبار السريع**:
- [ ] `curl -I https://coprra.com`
- [ ] `curl -I https://coprra.com.cdn.hstgr.net`
- [ ] `php artisan --version`

## ✅ **النتيجة**:
- موقع Laravel يعمل على Hostinger
- جميع الخدمات مفعلة
- نشر تلقائي (اختياري)

**الوقت المطلوب: 18 دقيقة فقط!** 🚀
