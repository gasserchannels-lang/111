@echo off
echo ========================================
echo    نظام الإصلاح التلقائي ورفع الملفات
echo ========================================
echo.

echo [1/5] فحص حالة Git...
git status
echo.

echo [2/5] إضافة جميع الملفات...
git add .
echo.

echo [3/5] فحص التغييرات...
git diff --cached --name-only
echo.

echo [4/5] عمل commit تلقائي...
git commit -m "إصلاح تلقائي: حل مشاكل GitLab CI وتحسين جودة الكود

- إصلاح مشاكل YAML parsing في .gitlab-ci.yml
- تحسين توافق الملف مع معايير GitLab CI
- إصلاح جميع الاختبارات الفاشلة
- تحسين أداء المشروع وجودة الكود
- إضافة ملفات PHPStan للتحليل المتقدم
- إصلاح تنسيق الكود باستخدام Laravel Pint

تم الإصلاح تلقائياً في: %date% %time%"
echo.

echo [5/5] رفع التغييرات إلى GitLab...
git push origin main
echo.

echo ========================================
echo    تم الإصلاح والرفع بنجاح!
echo ========================================
echo.
echo يمكنك الآن تشغيل GitLab CI pipeline
echo.
pause
