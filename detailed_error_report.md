# التقرير التفصيلي الكامل لمشاكل وأخطاء مشروع كوبرا

هذا المستند يحتوي على قائمة مفصلة بكل الأخطاء والمشاكل التي تم العثور عليها أثناء الفحص الشامل.

## جدول المحتويات
1.  [أخطاء التحليل الثابت (PHPStan)](#phpstan)
2.  [مشاكل الجودة والبنية (PHPInsights)](#phpinsights)
3.  [مشاكل تنسيق الكود (Pint)](#pint)
4.  [مشاكل الواجهة الأمامية (ESLint)](#eslint)
5.  [الاعتماديات القديمة](#dependencies)
6.  [مشاكل أخرى في الفحص](#other-issues)

---

<a name="phpstan"></a>
## 1. أخطاء التحليل الثابت (PHPStan) - (528 خطأ)

هذه أخطاء منطقية في الكود يجب التعامل معها بأولوية عالية.

**الملف: `app/Console/Commands/CheckDeploymentReadiness.php`**
-   **Line 27:** `Left side of || is always false.`

**الملف: `app/Contracts/EmailVerificationServiceInterface.php`**
-   **Line 15:** `Method ... sendVerificationEmail() has parameter $user with generic class App\Models\User but does not specify its types.`

**الملف: `app/Contracts/UserBanServiceInterface.php`**
-   **Line 16:** `Method ... banUser() has parameter $user with generic class App\Models\User but does not specify its types.`
-   **Line 22:** `Method ... unbanUser() has parameter $user with generic class App\Models\User but does not specify its types.`
-   **Line 28:** `Method ... isUserBanned() has parameter $user with generic class App\Models\User but does not specify its types.`
-   **Line 35:** `Method ... getBanInfo() has parameter $user with generic class App\Models\User but does not specify its types.`
-   **Line 41:** `Method ... getBannedUsers() return type with generic class App\Models\User does not specify its types.`
-   **Line 47:** `Method ... getUsersWithExpiredBans() return type with generic class App\Models\User does not specify its types.`
-   **Line 70:** `Method ... canBanUser() has parameter $user with generic class App\Models\User but does not specify its types.`
-   **Line 76:** `Method ... canUnbanUser() has parameter $user with generic class App\Models\User but does not specify its types.`
-   **Line 83:** `Method ... getBanHistory() has parameter $user with generic class App\Models\User but does not specify its types.`
-   **Line 89:** `Method ... extendBan() has parameter $user with generic class App\Models\User but does not specify its types.`
-   **Line 95:** `Method ... reduceBan() has parameter $user with generic class App\Models\User but does not specify its types.`

**الملف: `app/Http/Controllers/AdminController.php`**
-   **Line 96:** `Method ... toggleUserAdmin() has parameter $user with generic class App\Models\User but does not specify its types.`

**الملف: `app/Http/Controllers/Admin/DashboardController.php`**
-   **Line 104:** `Parameter #1 $column of static method ...where() expects ..., 'is_active' given.`
-   **Line 122:** `Parameter #1 $column of static method ...where() expects ..., 'is_featured' given.`
-   **Line 123:** `Parameter #1 $column of static method ...where() expects ..., 'stock_quantity' given.`
-   **Line 124:** `Parameter #1 $column of static method ...where() expects ..., 'stock_quantity' given.`
-   **Line 125:** `Parameter #1 $column of method ...where() expects ..., 'stock_quantity' given.`
-   **Line 433:** `Method ... getUserRegistrationChart() should return array<string, mixed> but returns list<array<string, int<0, max>|string>>.`

**الملف: `app/Http/Controllers/Api/BaseApiController.php`**
-   **Line 245:** `Method ... validateRequest() has parameter $messages with no value type specified in iterable type array.`
-   **Line 245:** `Method ... validateRequest() has parameter $rules with no value type specified in iterable type array.`
-   **Line 245:** `Method ... validateRequest() return type has no value type specified in iterable type array.`
-   **Line 262:** `Method ... getPaginationParams() return type has no value type specified in iterable type array.`
-   **Line 279:** `Method ... getSortingParams() return type has no value type specified in iterable type array.`
-   **Line 298:** `Method ... getFilteringParams() return type has no value type specified in iterable type array.`
-   **Line 311:** `Method ... getSearchParams() return type has no value type specified in iterable type array.`
-   **Line 403:** `Method ... getRateLimitInfo() return type has no value type specified in iterable type array.`

**الملف: `app/Http/Controllers/Api/PriceSearchController.php`**
-   **Line 57:** `Call to static method where() on an unknown class App\Http\Controllers\Api\Store.`
-   **Line 62:** `Caught class App\Http\Controllers\Api\Throwable not found.`
-   **Line 63:** `Access to an undefined property App\Http\Controllers\Api\PriceSearchController::$log.`
-   **Line 63:** `Call to method getMessage() on an unknown class App\Http\Controllers\Api\Throwable.`
-   **Line 136:** `Call to static method timeout() on an unknown class App\Http\Controllers\Api\Http.`
-   **Line 140:** `Caught class App\Http\Controllers\Api\Exception not found.`

... and 500+ more PHPStan errors ...

<a name="phpinsights"></a>
## 2. مشاكل الجودة والبنية (PHPInsights)

**ملخص التقييم:**
-   **Code:** 69.0%
-   **Complexity:** 72.5%
-   **Architecture:** 64.7%
-   **Style:** 83.1%

**أهم المشاكل:**

-   **[Code] Forbidden public property (6 issues):**
    -   `Services/ProcessResult.php:10`: Do not use public properties. Use method access instead.
    -   ...

-   **[Code] Forbidden setter (9 issues):**
    -   `Services/WatermarkService.php:446`: Setters are not allowed.
    -   ...

-   **[Code] Unused variable (19 issues):**
    -   `Services/SuspiciousActivityService.php:72`: Unused variable $userAgent.
    -   ...

-   **[Code] Disallow mixed type hint (304 issues):**
    -   `View/Composers/AppComposer.php:33`: Usage of "mixed" type hint is disallowed.
    -   ...

-   **[Complexity] High cyclomatic complexity (62 issues):**
    -   `Services/UserBanService.php`: 14 cyclomatic complexity
    -   `Services/VulnerabilityScanService.php`: 46 cyclomatic complexity
    -   ...

-   **[Architecture] Normal classes are forbidden (129 issues):**
    -   `Services/VulnerabilityScanService.php`
    -   ...

-   **[Architecture] Function length (112 issues):**
    -   `Services/WatermarkService.php:105`: Your function is too long. Currently using 31 lines. Can be up to 20 lines.
    -   ...

-   **[Style] Line length (787 issues):**
    -   `Services/WatermarkService.php:340`: Line exceeds 80 characters; contains 82 characters
    -   ...

... and many more PHPInsights issues ...

<a name="pint"></a>
## 3. مشاكل تنسيق الكود (Pint) - (168 مخالفة)

تم العثور على مشاكل في التنسيق في 282 ملف. هذه المشاكل يمكن إصلاحها تلقائياً.

-   `app/Console/Commands/AgentProposeFixCommand.php`: `concat_space`, `not_operator_with_successor_space`
-   `app/Console/Commands/CheckDeploymentReadiness.php`: `concat_space`, `not_operator_with_successor_space`, `phpdoc_align`
-   `app/Console/Commands/CleanupOldDataCommand.php`: `cast_spaces`, `not_operator_with_successor_space`
-   `app/Http/Controllers/Admin/DashboardController.php`: `class_attributes_separation`, `concat_space`, `phpdoc_separation`
-   `database/migrations/0001_01_01_000000_create_users_table.php`: `new_with_parentheses`, `class_definition`, `braces_position`

... and 277 more files ...

<a name="eslint"></a>
## 4. مشاكل الواجهة الأمامية (ESLint) - (1238 مشكلة)

-   **المشكلة الأكثر تكراراً:** `Expected linebreaks to be 'LF' but found 'CRLF'` (مئات المرات).
-   **أخطاء حرجة:**
    -   `'localStorage' is not defined (no-undef)` in `resources/js/state/Store.js`
    -   `'navigator' is not defined (no-undef)` in `resources/js/utils/ErrorTracker.js`
    -   `'fetch' is not defined (no-undef)` in `resources/js/utils/ErrorTracker.js`
-   **تحذيرات:**
    -   `Unexpected console statement (no-console)`: 5 warnings.
    -   `The ".eslintignore" file is no longer supported.`

**قائمة جزئية بالملفات المتأثرة:**
-   `resources/js/animations/image-effects.js`: 500+ errors, mostly `linebreak-style`.
-   `resources/js/state/Store.js`: 300+ errors, `linebreak-style`, `no-undef`, `no-console`.
-   `resources/js/utils/ErrorTracker.js`: 200+ errors, `linebreak-style`, `no-undef`.

<a name="dependencies"></a>
## 5. الاعتماديات القديمة (Outdated Dependencies)

### PHP (Composer)
-   **تحديثات بسيطة (آمنة):**
    -   `deptrac/deptrac`: 4.1.0 -> 4.2.0
    -   `rector/rector`: 2.1.6 -> 2.1.7
    -   `sentry/sentry-laravel`: 4.15.3 -> 4.16.0
    -   `friendsofphp/php-cs-fixer`: 3.87.1 -> 3.87.2
    -   `phpstan/phpstan`: 2.1.22 -> 2.1.23
-   **تحديثات رئيسية (تتطلب حذراً):**
    -   `spatie/error-solutions`: 1.1.3 -> 2.0.1
    -   `spatie/flare-client-php`: 1.10.1 -> 2.1.0

### JavaScript (NPM)
-   **تحديث رئيسي:**
    -   `laravel-vite-plugin`: 1.3.0 -> 2.0.1

<a name="other-issues"></a>
## 6. مشاكل أخرى في الفحص

-   **أداة Psalm:** فشلت في العمل بشكل متكرر بسبب أخطاء في ملف الإعدادات `psalm.xml`.
-   **فحص الأمان:** أداة `security-checker` لم تعمل بسبب مشكلة في شهادة SSL في بيئة التشغيل المحلية.
-   **الاختبارات الآلية:** مجموعة الاختبارات تتجاوز المهلة الزمنية (300 ثانية) وتفشل في الإكمال.
