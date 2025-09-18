# دليل تطبيق تصحيحات حل النزاعات الهيكلية

## نظرة عامة

هذا المجلد يحتوي على 18 ملف تصحيح لحل النزاعات الهيكلية في المشروع. يجب تطبيق هذه التصحيحات بالترتيب المحدد لضمان نجاح العملية.

## ترتيب التطبيق

### الجزء الأول: توحيد إعدادات PHPUnit

1. `part1_unified_phpunit.xml.patch` - تحديث الملف الرئيسي
2. `part1_delete_phpunit_xml_bak.patch` - حذف الملف الاحتياطي
3. `part1_delete_phpunit_testing_xml.patch` - حذف ملف الاختبار
4. `part1_delete_phpunit_strict_xml.patch` - حذف ملف الصارم

### الجزء الثاني: إصلاح إعدادات قاعدة البيانات

5. `part2_fix_gitlab_ci_database.patch` - تحديث إعدادات CI

### الجزء الثالث: إعادة تسمية الملفات المكررة

6. `part3_rename_ai_model_performance_test.patch`
7. `part3_rename_example_tests.patch`
8. `part3_rename_authentication_tests.patch`
9. `part3_rename_controller_tests.patch`
10. `part3_rename_database_migration_tests.patch`
11. `part3_rename_remaining_controller_tests.patch`
12. `part3_rename_middleware_and_performance_tests.patch`
13. `part3_rename_performance_tests.patch`
14. `part3_rename_data_tests.patch`
15. `part3_rename_final_tests.patch`
16. `part3_rename_testing_tests.patch`

### الجزء الرابع: تنظيف CI/CD

17. `part4_delete_gitlab_ci_old.patch` - حذف ملف CI القديم

### الجزء الخامس: إصلاح Docker

18. `part5_fix_dockerignore.patch` - إصلاح ملف .dockerignore

## كيفية التطبيق

### باستخدام Git

```bash
# تطبيق جميع التصحيحات
for patch in patches/conflict-resolution/*.patch; do
    git apply "$patch"
done
```

### باستخدام patch command

```bash
# تطبيق تصحيح واحد
patch -p1 < patches/conflict-resolution/part1_unified_phpunit.xml.patch

# تطبيق جميع التصحيحات
for patch in patches/conflict-resolution/*.patch; do
    patch -p1 < "$patch"
done
```

### باستخدام PowerShell (Windows)

```powershell
# تطبيق تصحيح واحد
git apply patches/conflict-resolution/part1_unified_phpunit.xml.patch

# تطبيق جميع التصحيحات
Get-ChildItem patches/conflict-resolution/*.patch | ForEach-Object { git apply $_.FullName }
```

## التحقق من التطبيق

### 1. التحقق من ملفات PHPUnit

```bash
# يجب أن يكون هناك ملف واحد فقط
ls phpunit*.xml
# يجب أن يظهر: phpunit.xml فقط
```

### 2. التحقق من أسماء الملفات المكررة

```bash
# يجب ألا تظهر أسماء مكررة
find tests -name "*.php" -type f | sort | uniq -d
```

### 3. التحقق من إعدادات CI

```bash
# يجب ألا يحتوي على MySQL services
grep -n "mysql" .gitlab-ci.yml
```

### 4. التحقق من Docker

```bash
# يجب ألا يحتوي على /tests
grep -n "/tests" .dockerignore
```

## ملاحظات مهمة

1. **النسخ الاحتياطية**: تأكد من إنشاء نسخة احتياطية قبل التطبيق
2. **الترتيب**: يجب تطبيق التصحيحات بالترتيب المحدد
3. **الاختبار**: قم بتشغيل الاختبارات بعد كل جزء للتأكد من عدم كسر شيء
4. **المراجعة**: راجع التغييرات قبل الالتزام

## استكشاف الأخطاء

### إذا فشل تطبيق تصحيح

```bash
# إعادة تعيين التغييرات
git checkout -- <file-name>

# تطبيق التصحيح مرة أخرى
git apply <patch-file>
```

### إذا ظهرت أخطاء في الاختبارات

```bash
# تشغيل اختبارات محددة
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

## الدعم

إذا واجهت أي مشاكل، راجع التقرير الرئيسي `conflict_resolution_report.md` للحصول على تفاصيل إضافية.
