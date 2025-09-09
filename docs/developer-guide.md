# دليل المطورين الجدد - COPRRA

## نظرة عامة

مرحباً بك في مشروع COPRRA! هذا الدليل سيساعدك على البدء في التطوير والمساهمة في المشروع.

## جدول المحتويات

1. [إعداد البيئة](#إعداد-البيئة)
2. [هيكل المشروع](#هيكل-المشروع)
3. [المعايير والاتفاقيات](#المعايير-والاتفاقيات)
4. [سير العمل](#سير-العمل)
5. [الاختبارات](#الاختبارات)
6. [النشر](#النشر)
7. [المساهمة](#المساهمة)

## إعداد البيئة

### المتطلبات

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- Redis
- Git

### التثبيت

1. **استنساخ المشروع**
```bash
git clone https://github.com/your-username/coprra.git
cd coprra
```

2. **تثبيت التبعيات**
```bash
# PHP dependencies
composer install

# Node.js dependencies
npm install
```

3. **إعداد البيئة**
```bash
cp .env.example .env
php artisan key:generate
```

4. **إعداد قاعدة البيانات**
```bash
php artisan migrate
php artisan db:seed
```

5. **بناء الأصول**
```bash
npm run build
```

6. **تشغيل الخادم**
```bash
php artisan serve
```

## هيكل المشروع

```
coprra/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/           # API Controllers
│   │   │   └── Admin/         # Admin Controllers
│   │   ├── Middleware/        # Custom Middleware
│   │   └── Requests/          # Form Requests
│   ├── Models/                # Eloquent Models
│   ├── Services/              # Business Logic Services
│   ├── Jobs/                  # Queue Jobs
│   ├── Policies/              # Authorization Policies
│   └── Exceptions/            # Custom Exceptions
├── config/                    # Configuration Files
├── database/
│   ├── migrations/            # Database Migrations
│   ├── seeders/              # Database Seeders
│   └── factories/            # Model Factories
├── resources/
│   ├── views/                # Blade Templates
│   ├── js/                   # JavaScript Files
│   └── css/                  # CSS Files
├── routes/
│   ├── web.php               # Web Routes
│   ├── api.php               # API Routes
│   └── admin.php             # Admin Routes
├── tests/                    # Test Files
├── docs/                     # Documentation
└── docker/                   # Docker Configuration
```

## المعايير والاتفاقيات

### تسمية الملفات

- **Controllers**: `PascalCase` (مثال: `ProductController.php`)
- **Models**: `PascalCase` (مثال: `Product.php`)
- **Services**: `PascalCase` (مثال: `ProductService.php`)
- **Views**: `kebab-case` (مثال: `product-details.blade.php`)
- **Migrations**: `snake_case` (مثال: `create_products_table.php`)

### تسمية المتغيرات

- **PHP**: `camelCase` (مثال: `$productName`)
- **JavaScript**: `camelCase` (مثال: `productName`)
- **CSS**: `kebab-case` (مثال: `.product-name`)

### تسمية الدوال

- **PHP**: `camelCase` (مثال: `getProductById()`)
- **JavaScript**: `camelCase` (مثال: `fetchProducts()`)

### تسمية قواعد البيانات

- **Tables**: `snake_case` (مثال: `product_categories`)
- **Columns**: `snake_case` (مثال: `created_at`)
- **Indexes**: `snake_case` (مثال: `idx_products_name`)

## سير العمل

### 1. إنشاء فرع جديد

```bash
git checkout -b feature/new-feature
```

### 2. التطوير

- اكتب الكود وفقاً للمعايير
- أضف التعليقات التوضيحية
- اكتب الاختبارات

### 3. الاختبار

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ProductTest

# Run with coverage
php artisan test --coverage
```

### 4. التحقق من الجودة

```bash
# PHP Code Style
./vendor/bin/pint

# Static Analysis
./vendor/bin/phpstan analyse

# Security Check
composer audit
```

### 5. الالتزام

```bash
git add .
git commit -m "feat: add new product feature"
```

### 6. الدفع

```bash
git push origin feature/new-feature
```

### 7. إنشاء Pull Request

- اذهب إلى GitHub
- أنشئ Pull Request
- اكتب وصفاً واضحاً للتغييرات
- اطلب مراجعة من المطورين

## الاختبارات

### أنواع الاختبارات

1. **Unit Tests**: اختبار الوحدات الفردية
2. **Feature Tests**: اختبار الميزات الكاملة
3. **Integration Tests**: اختبار التكامل
4. **Performance Tests**: اختبار الأداء

### كتابة الاختبارات

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;

class ProductTest extends TestCase
{
    public function test_can_create_product()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 100.00,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 100.00,
        ]);
    }
}
```

### تشغيل الاختبارات

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Unit/ProductTest.php

# Specific test method
php artisan test --filter=test_can_create_product

# With coverage
php artisan test --coverage
```

## النشر

### البيئات

1. **Development**: بيئة التطوير المحلية
2. **Staging**: بيئة الاختبار
3. **Production**: بيئة الإنتاج

### خطوات النشر

1. **تحديث الكود**
```bash
git pull origin main
```

2. **تثبيت التبعيات**
```bash
composer install --no-dev --optimize-autoloader
npm ci --production
```

3. **بناء الأصول**
```bash
npm run build
```

4. **تشغيل المهام**
```bash
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5. **إعادة تشغيل الخدمات**
```bash
php artisan queue:restart
```

## المساهمة

### كيفية المساهمة

1. **Fork** المشروع
2. **Clone** النسخة الخاصة بك
3. **إنشاء فرع** للميزة الجديدة
4. **التطوير** والاختبار
5. **إنشاء Pull Request**

### أنواع المساهمات

- **Bug Fixes**: إصلاح الأخطاء
- **New Features**: ميزات جديدة
- **Documentation**: تحسين التوثيق
- **Performance**: تحسين الأداء
- **Security**: تحسين الأمان

### معايير المساهمة

- اتبع معايير الكود
- اكتب اختبارات شاملة
- حدث التوثيق
- اكتب رسائل commit واضحة
- اطلب مراجعة الكود

## الأدوات المفيدة

### IDE/Editors

- **PhpStorm**: IDE متقدم لـ PHP
- **VS Code**: محرر مجاني مع إضافات
- **Sublime Text**: محرر سريع وخفيف

### إضافات VS Code

- **PHP Intelephense**: IntelliSense للـ PHP
- **Laravel Blade Snippets**: Blade snippets
- **GitLens**: Git integration
- **Prettier**: Code formatting
- **ESLint**: JavaScript linting

### أدوات سطر الأوامر

- **Laravel Tinker**: REPL للـ Laravel
- **Laravel Debugbar**: Debug toolbar
- **Laravel Telescope**: Debug and monitoring

## استكشاف الأخطاء

### مشاكل شائعة

1. **خطأ في التصريح**
```bash
chmod -R 755 storage bootstrap/cache
```

2. **مشكلة في Composer**
```bash
composer clear-cache
composer install --no-cache
```

3. **مشكلة في NPM**
```bash
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

4. **مشكلة في قاعدة البيانات**
```bash
php artisan migrate:fresh --seed
```

### سجلات الأخطاء

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/error.log

# MySQL logs
tail -f /var/log/mysql/error.log
```

## الموارد المفيدة

### Laravel Documentation
- [Laravel 11.x Documentation](https://laravel.com/docs/11.x)
- [Laravel API Reference](https://laravel.com/api/11.x)

### PHP Resources
- [PHP Manual](https://www.php.net/manual/)
- [PSR Standards](https://www.php-fig.org/psr/)

### Frontend Resources
- [Vue.js Documentation](https://vuejs.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Vite Documentation](https://vitejs.dev/)

### Git Resources
- [Git Documentation](https://git-scm.com/doc)
- [GitHub Flow](https://guides.github.com/introduction/flow/)

## الدعم والمساعدة

### الحصول على المساعدة

1. **التحقق من التوثيق** أولاً
2. **البحث في Issues** الموجودة
3. **إنشاء Issue جديد** إذا لزم الأمر
4. **الانضمام للمحادثة** في Slack/Discord

### التواصل

- **Email**: dev@coprra.com
- **Slack**: #coprra-dev
- **GitHub Issues**: للمشاكل والأخطاء
- **GitHub Discussions**: للمناقشات العامة

## الخلاصة

هذا الدليل يغطي الأساسيات للبدء في التطوير على مشروع COPRRA. إذا كان لديك أي أسئلة أو تحتاج مساعدة، لا تتردد في التواصل معنا!

**نصائح مهمة:**

- اقرأ الكود الموجود قبل كتابة كود جديد
- اتبع معايير المشروع
- اكتب اختبارات شاملة
- اطلب مراجعة الكود دائماً
- حافظ على التوثيق محدثاً

**حظاً سعيداً في التطوير!** 🚀
