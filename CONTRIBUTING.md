# دليل المساهمة - COPRRA

نشكرك على اهتمامك بالمساهمة في مشروع COPRRA! هذا الدليل سيساعدك على المساهمة بشكل فعال.

## جدول المحتويات

- [كيفية المساهمة](#كيفية-المساهمة)
- [إعداد البيئة](#إعداد-البيئة)
- [قواعد الكود](#قواعد-الكود)
- [عملية التطوير](#عملية-التطوير)
- [الاختبارات](#الاختبارات)
- [التوثيق](#التوثيق)
- [الإبلاغ عن الأخطاء](#الإبلاغ-عن-الأخطاء)
- [اقتراح الميزات](#اقتراح-الميزات)
- [الأسئلة الشائعة](#الأسئلة-الشائعة)

## كيفية المساهمة

### أنواع المساهمات

1. **إصلاح الأخطاء**: إصلاح الأخطاء الموجودة
2. **إضافة ميزات**: إضافة ميزات جديدة
3. **تحسين الأداء**: تحسين أداء التطبيق
4. **تحسين التوثيق**: تحسين التوثيق
5. **تحسين الاختبارات**: إضافة أو تحسين الاختبارات
6. **تحسين التصميم**: تحسين واجهة المستخدم
7. **تحسين الأمان**: تحسين أمان التطبيق
8. **تحسين التوافق**: تحسين التوافق مع المتصفحات

### خطوات المساهمة

1. **Fork** المشروع
2. **Clone** المشروع إلى جهازك
3. إنشاء **branch** جديد للميزة
4. إجراء التغييرات المطلوبة
5. كتابة **اختبارات** للتغييرات
6. تحديث **التوثيق** عند الحاجة
7. **Commit** التغييرات
8. **Push** التغييرات إلى GitHub
9. إنشاء **Pull Request**

## إعداد البيئة

### المتطلبات

- **PHP**: 8.2 أو أحدث
- **Composer**: 2.0 أو أحدث
- **Node.js**: 18.0 أو أحدث
- **NPM**: 8.0 أو أحدث
- **MySQL**: 8.0 أو أحدث
- **Redis**: 6.0 أو أحدث

### التثبيت

```bash
# نسخ المشروع
git clone https://github.com/your-username/coprra.git
cd coprra

# تثبيت التبعيات
composer install
npm install

# إعداد البيئة
cp .env.example .env
php artisan key:generate

# إعداد قاعدة البيانات
php artisan migrate
php artisan db:seed

# بناء الأصول
npm run build
```

### إعداد IDE

#### VS Code
```json
{
  "php.suggest.basic": false,
  "php.validate.enable": true,
  "php.validate.executablePath": "/usr/bin/php",
  "phpcs.enable": true,
  "phpcs.executablePath": "./vendor/bin/phpcs",
  "phpcs.standard": "PSR12",
  "phpstan.enabled": true,
  "phpstan.path": "./vendor/bin/phpstan",
  "psalm.enabled": true,
  "psalm.path": "./vendor/bin/psalm"
}
```

#### PhpStorm
- تفعيل **PHP CS Fixer**
- تفعيل **PHPStan**
- تفعيل **Psalm**
- تفعيل **Laravel IDE Helper**

## قواعد الكود

### PHP

#### PSR-12
```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExampleService
{
    public function __construct(
        private readonly Cache $cache,
        private readonly DB $db
    ) {
    }

    public function exampleMethod(string $parameter): array
    {
        // Implementation
        return [];
    }
}
```

#### التعليقات
```php
/**
 * فئة خدمة المثال
 * 
 * @package App\Services
 * @author Your Name <your.email@example.com>
 * @since 1.0.0
 */
class ExampleService
{
    /**
     * مثال على الطريقة
     * 
     * @param string $parameter معامل النص
     * @return array النتيجة
     * @throws \InvalidArgumentException عند وجود خطأ في المعامل
     */
    public function exampleMethod(string $parameter): array
    {
        // Implementation
        return [];
    }
}
```

### JavaScript

#### ES6+
```javascript
/**
 * فئة مثال JavaScript
 * @class ExampleClass
 */
class ExampleClass {
    /**
     * إنشاء مثيل جديد
     * @param {Object} options الخيارات
     */
    constructor(options = {}) {
        this.options = options;
    }

    /**
     * مثال على الطريقة
     * @param {string} parameter المعامل
     * @returns {Promise<Array>} النتيجة
     */
    async exampleMethod(parameter) {
        // Implementation
        return [];
    }
}

export default ExampleClass;
```

### CSS

#### BEM Methodology
```css
/* Block */
.product-card {
    display: flex;
    flex-direction: column;
}

/* Element */
.product-card__image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

/* Modifier */
.product-card--featured {
    border: 2px solid #007bff;
}

.product-card--featured .product-card__image {
    height: 250px;
}
```

## عملية التطوير

### Git Workflow

#### Branch Naming
- `feature/description`: للميزات الجديدة
- `bugfix/description`: لإصلاح الأخطاء
- `hotfix/description`: للإصلاحات العاجلة
- `refactor/description`: لإعادة هيكلة الكود
- `docs/description`: للتوثيق
- `test/description`: للاختبارات

#### Commit Messages
```
type(scope): description

Detailed description if needed

Closes #123
```

**Types:**
- `feat`: ميزة جديدة
- `fix`: إصلاح خطأ
- `docs`: توثيق
- `style`: تنسيق
- `refactor`: إعادة هيكلة
- `test`: اختبارات
- `chore`: مهام

**Examples:**
```
feat(api): add product search endpoint
fix(auth): resolve login validation issue
docs(readme): update installation guide
```

### Code Review

#### Checklist
- [ ] الكود يتبع معايير PSR-12
- [ ] الاختبارات مكتوبة ومتجاوزة
- [ ] التوثيق محدث
- [ ] لا توجد أخطاء في PHPStan
- [ ] لا توجد أخطاء في Psalm
- [ ] الأداء محسن
- [ ] الأمان محسن
- [ ] التوافق مع المتصفحات

## الاختبارات

### PHP Tests

#### Unit Tests
```php
<?php

namespace Tests\Unit\Services;

use App\Services\ExampleService;
use Tests\TestCase;

class ExampleServiceTest extends TestCase
{
    public function test_example_method_returns_array(): void
    {
        $service = new ExampleService();
        $result = $service->exampleMethod('test');
        
        $this->assertIsArray($result);
    }
}
```

#### Feature Tests
```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ProductApiTest extends TestCase
{
    public function test_can_get_products(): void
    {
        $response = $this->getJson('/api/products');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'price'
                        ]
                    ]
                ]);
    }
}
```

### JavaScript Tests

#### Jest Tests
```javascript
import ExampleClass from '../src/ExampleClass';

describe('ExampleClass', () => {
    let instance;

    beforeEach(() => {
        instance = new ExampleClass();
    });

    test('should return array from exampleMethod', async () => {
        const result = await instance.exampleMethod('test');
        expect(Array.isArray(result)).toBe(true);
    });
});
```

### Running Tests

```bash
# PHP Tests
php artisan test
php artisan test --coverage

# JavaScript Tests
npm test
npm run test:coverage

# All Tests
npm run test:all
```

## التوثيق

### README Files
- **README.md**: نظرة عامة على المشروع
- **API_DOCUMENTATION.md**: وثائق API
- **DEPLOYMENT_GUIDE.md**: دليل النشر
- **CHANGELOG.md**: سجل التغييرات

### Code Documentation
- **PHPDoc**: توثيق PHP
- **JSDoc**: توثيق JavaScript
- **CSS Comments**: تعليقات CSS
- **Inline Comments**: تعليقات داخلية

### Examples
```php
/**
 * البحث عن أفضل عرض للمنتج
 * 
 * @param string $product اسم المنتج
 * @param string $country رمز البلد
 * @return array النتيجة
 * 
 * @example
 * $result = $service->findBestOffer('iPhone 15', 'US');
 * // Returns: ['store' => 'Apple', 'price' => 999.99]
 */
public function findBestOffer(string $product, string $country): array
{
    // Implementation
}
```

## الإبلاغ عن الأخطاء

### Bug Report Template

```markdown
**وصف الخطأ**
وصف واضح ومفصل للخطأ.

**خطوات إعادة الإنتاج**
1. اذهب إلى '...'
2. انقر على '...'
3. مرر لأسفل إلى '...'
4. شاهد الخطأ

**السلوك المتوقع**
وصف ما كنت تتوقع حدوثه.

**لقطات الشاشة**
إذا أمكن، أضف لقطات شاشة للمساعدة في شرح المشكلة.

**البيئة**
- OS: [e.g. Windows 10]
- Browser: [e.g. Chrome 91]
- Version: [e.g. 1.0.0]

**معلومات إضافية**
أي معلومات أخرى مفيدة حول المشكلة.
```

### Security Issues

للإبلاغ عن مشاكل الأمان، يرجى:
1. **عدم** إنشاء issue عام
2. إرسال بريد إلكتروني إلى: security@coprra.com
3. وصف المشكلة بالتفصيل
4. إرفاق أي أدلة أو أمثلة

## اقتراح الميزات

### Feature Request Template

```markdown
**هل تريد اقتراح ميزة؟**
وصف واضح ومفصل للميزة المقترحة.

**هل هذه الميزة مرتبطة بمشكلة؟**
وصف المشكلة. مثال: "أنا محبط عندما..."

**وصف الحل المطلوب**
وصف واضح ومفصل لما تريد أن يحدث.

**وصف البدائل**
وصف واضح ومفصل لأي حلول أو ميزات بديلة فكرت فيها.

**سياق إضافي**
أضف أي سياق آخر أو لقطات شاشة حول اقتراح الميزة هنا.
```

## الأسئلة الشائعة

### Q: كيف أبدأ في المساهمة؟
A: ابدأ بقراءة هذا الدليل، ثم اختر issue بسيط للمبتدئين.

### Q: ما هي المتطلبات للمساهمة؟
A: معرفة أساسية بـ PHP, Laravel, JavaScript, و Git.

### Q: كيف أتأكد من أن كودي يتبع المعايير؟
A: استخدم PHP CS Fixer, PHPStan, و Psalm.

### Q: هل يمكنني المساهمة في التوثيق فقط؟
A: نعم، التوثيق مهم جداً ونقدر المساهمات فيه.

### Q: كيف أتأكد من أن اختباراتي صحيحة؟
A: تأكد من أن الاختبارات تغطي جميع الحالات وتتجاوز بنجاح.

### Q: هل يمكنني اقتراح ميزات جديدة؟
A: نعم، نرحب بجميع الاقتراحات المفيدة.

## التواصل

- **GitHub Issues**: للمناقشات العامة
- **Discord**: للدردشة المباشرة
- **Email**: للاستفسارات الخاصة
- **Twitter**: للأخبار والتحديثات

## الترخيص

هذا المشروع مرخص تحت [MIT License](LICENSE).

---

**شكراً لك على المساهمة في COPRRA!** 🚀