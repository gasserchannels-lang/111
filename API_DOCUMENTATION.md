# وثائق API - COPRRA

## نظرة عامة

COPRRA API يوفر واجهة برمجية شاملة لمقارنة الأسعار وإدارة المنتجات. جميع الطلبات تستخدم JSON وتتطلب مصادقة.

## Base URL
```
https://your-domain.com/api
```

## المصادقة

### Bearer Token
```http
Authorization: Bearer {your-token}
```

### API Key
```http
X-API-Key: {your-api-key}
```

## رموز الحالة

| الكود | المعنى |
|-------|--------|
| 200 | نجح الطلب |
| 201 | تم إنشاء المورد |
| 400 | طلب غير صالح |
| 401 | غير مصرح |
| 403 | محظور |
| 404 | غير موجود |
| 422 | خطأ في التحقق |
| 500 | خطأ في الخادم |

## المنتجات

### الحصول على قائمة المنتجات
```http
GET /products
```

**المعاملات:**
- `page` (int): رقم الصفحة
- `per_page` (int): عدد العناصر في الصفحة
- `search` (string): البحث في اسم المنتج
- `category_id` (int): تصفية حسب الفئة
- `brand_id` (int): تصفية حسب الماركة
- `min_price` (float): الحد الأدنى للسعر
- `max_price` (float): الحد الأقصى للسعر
- `sort` (string): ترتيب النتائج (price_asc, price_desc, name_asc, name_desc)

**مثال:**
```http
GET /products?search=iphone&category_id=1&min_price=500&max_price=1000&sort=price_asc
```

**الاستجابة:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "iPhone 15 Pro",
      "slug": "iphone-15-pro",
      "description": "Latest iPhone with advanced features",
      "price": 999.99,
      "compare_at_price": 1099.99,
      "image": "https://example.com/images/iphone-15-pro.jpg",
      "is_active": true,
      "category": {
        "id": 1,
        "name": "Smartphones"
      },
      "brand": {
        "id": 1,
        "name": "Apple"
      },
      "store": {
        "id": 1,
        "name": "Apple Store"
      },
      "created_at": "2025-01-09T10:00:00Z",
      "updated_at": "2025-01-09T10:00:00Z"
    }
  ],
  "links": {
    "first": "https://api.example.com/products?page=1",
    "last": "https://api.example.com/products?page=10",
    "prev": null,
    "next": "https://api.example.com/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 20,
    "to": 20,
    "total": 200
  }
}
```

### الحصول على منتج محدد
```http
GET /products/{id}
```

**الاستجابة:**
```json
{
  "data": {
    "id": 1,
    "name": "iPhone 15 Pro",
    "slug": "iphone-15-pro",
    "description": "Latest iPhone with advanced features",
    "price": 999.99,
    "compare_at_price": 1099.99,
    "image": "https://example.com/images/iphone-15-pro.jpg",
    "is_active": true,
    "category": {
      "id": 1,
      "name": "Smartphones"
    },
    "brand": {
      "id": 1,
      "name": "Apple"
    },
    "store": {
      "id": 1,
      "name": "Apple Store"
    },
    "offers": [
      {
        "id": 1,
        "store": {
          "id": 1,
          "name": "Apple Store"
        },
        "price": 999.99,
        "currency": "USD",
        "product_url": "https://apple.com/iphone-15-pro",
        "affiliate_url": "https://apple.com/iphone-15-pro?ref=coprra",
        "in_stock": true,
        "condition": "new",
        "rating": 4.8,
        "reviews_count": 1250
      }
    ],
    "reviews": [
      {
        "id": 1,
        "user": {
          "id": 1,
          "name": "John Doe"
        },
        "title": "Great phone!",
        "content": "Amazing features and performance",
        "rating": 5,
        "is_verified_purchase": true,
        "created_at": "2025-01-09T10:00:00Z"
      }
    ],
    "created_at": "2025-01-09T10:00:00Z",
    "updated_at": "2025-01-09T10:00:00Z"
  }
}
```

## البحث في الأسعار

### البحث عن أفضل عرض
```http
POST /price-search/best-offer
```

**الطلب:**
```json
{
  "product": "iPhone 15 Pro",
  "country": "US"
}
```

**الاستجابة:**
```json
{
  "data": {
    "product": {
      "id": 1,
      "name": "iPhone 15 Pro",
      "image": "https://example.com/images/iphone-15-pro.jpg"
    },
    "best_offer": {
      "id": 1,
      "store": {
        "id": 1,
        "name": "Apple Store",
        "logo": "https://example.com/logos/apple.png"
      },
      "price": 999.99,
      "currency": "USD",
      "product_url": "https://apple.com/iphone-15-pro",
      "affiliate_url": "https://apple.com/iphone-15-pro?ref=coprra",
      "in_stock": true,
      "condition": "new",
      "rating": 4.8,
      "reviews_count": 1250,
      "savings": 100.00,
      "savings_percentage": 9.09
    },
    "all_offers": [
      {
        "id": 1,
        "store": {
          "id": 1,
          "name": "Apple Store"
        },
        "price": 999.99,
        "currency": "USD",
        "in_stock": true,
        "condition": "new"
      },
      {
        "id": 2,
        "store": {
          "id": 2,
          "name": "Amazon"
        },
        "price": 1049.99,
        "currency": "USD",
        "in_stock": true,
        "condition": "new"
      }
    ],
    "search_metadata": {
      "total_offers": 2,
      "search_time": "0.045s",
      "searched_at": "2025-01-09T10:00:00Z"
    }
  }
}
```

## قائمة الأمنيات

### الحصول على قائمة الأمنيات
```http
GET /wishlist
```

**الاستجابة:**
```json
{
  "data": [
    {
      "id": 1,
      "product": {
        "id": 1,
        "name": "iPhone 15 Pro",
        "price": 999.99,
        "image": "https://example.com/images/iphone-15-pro.jpg"
      },
      "notes": "Birthday gift",
      "created_at": "2025-01-09T10:00:00Z"
    }
  ]
}
```

### إضافة منتج لقائمة الأمنيات
```http
POST /wishlist
```

**الطلب:**
```json
{
  "product_id": 1,
  "notes": "Birthday gift"
}
```

### حذف منتج من قائمة الأمنيات
```http
DELETE /wishlist/{id}
```

## تنبيهات الأسعار

### الحصول على تنبيهات الأسعار
```http
GET /price-alerts
```

### إنشاء تنبيه سعر
```http
POST /price-alerts
```

**الطلب:**
```json
{
  "product_id": 1,
  "target_price": 800.00,
  "currency": "USD",
  "repeat_alert": true
}
```

### تحديث تنبيه سعر
```http
PUT /price-alerts/{id}
```

### حذف تنبيه سعر
```http
DELETE /price-alerts/{id}
```

## المراجعات

### الحصول على مراجعات منتج
```http
GET /products/{id}/reviews
```

### إنشاء مراجعة
```http
POST /reviews
```

**الطلب:**
```json
{
  "product_id": 1,
  "title": "Great product!",
  "content": "Amazing features and performance",
  "rating": 5,
  "is_verified_purchase": true
}
```

## الإحصائيات

### إحصائيات المنتج
```http
GET /products/{id}/stats
```

**الاستجابة:**
```json
{
  "data": {
    "product": {
      "id": 1,
      "name": "iPhone 15 Pro"
    },
    "stats": {
      "wishlist_count": 150,
      "price_alerts_count": 25,
      "reviews_count": 1250,
      "average_rating": 4.8,
      "offers_count": 5,
      "price_history": [
        {
          "date": "2025-01-01",
          "price": 1099.99
        },
        {
          "date": "2025-01-09",
          "price": 999.99
        }
      ]
    }
  }
}
```

## الأخطاء

### مثال على خطأ التحقق
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "product_id": [
      "The product id field is required."
    ],
    "target_price": [
      "The target price must be a number.",
      "The target price must be greater than 0."
    ]
  }
}
```

### مثال على خطأ المصادقة
```json
{
  "message": "Unauthenticated."
}
```

## Rate Limiting

- **الحد الأقصى**: 1000 طلب في الساعة
- **الرؤوس المطلوبة**:
  - `X-RateLimit-Limit`: الحد الأقصى للطلبات
  - `X-RateLimit-Remaining`: الطلبات المتبقية
  - `X-RateLimit-Reset`: وقت إعادة تعيين العداد

## SDKs

### JavaScript
```javascript
import CoprraAPI from 'coprra-js-sdk';

const api = new CoprraAPI({
  baseURL: 'https://api.example.com',
  apiKey: 'your-api-key'
});

// البحث عن أفضل عرض
const bestOffer = await api.priceSearch.bestOffer({
  product: 'iPhone 15 Pro',
  country: 'US'
});
```

### PHP
```php
use Coprra\CoprraAPI;

$api = new CoprraAPI([
    'base_url' => 'https://api.example.com',
    'api_key' => 'your-api-key'
]);

// البحث عن أفضل عرض
$bestOffer = $api->priceSearch()->bestOffer([
    'product' => 'iPhone 15 Pro',
    'country' => 'US'
]);
```

## الدعم

للحصول على الدعم، يرجى التواصل معنا:
- **البريد الإلكتروني**: support@coprra.com
- **الوثائق**: https://docs.coprra.com
- **GitHub**: https://github.com/coprra/api
