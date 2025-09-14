<?php

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Initialize and Clean up Mockery
|--------------------------------------------------------------------------
*/

// تهيئة وتنظيف Mockery قبل بدء الاختبارات
if (class_exists('Mockery')) {
    // إعادة تعيين container Mockery
    Mockery::resetContainer();

    // تفعيل global helpers
    Mockery::globalHelpers();

    // تنظيف أي static references سابقة
    try {
        $container = Mockery::getContainer();
        if ($container) {
            $container->mockery_teardown();
        }
    } catch (\Exception $e) {
        // تجاهل الأخطاء في التنظيف الأولي
    }

    // إعادة تعيين container مرة أخرى بعد التنظيف
    Mockery::resetContainer();
}

/*
|--------------------------------------------------------------------------
| Set The Default Timezone
|--------------------------------------------------------------------------
*/

date_default_timezone_set('UTC');

/*
|--------------------------------------------------------------------------
| Bootstrap The Application
|--------------------------------------------------------------------------
*/

$app = require __DIR__.'/../bootstrap/app.php';

// تأكد من أن التطبيق في وضع الاختبار
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/*
|--------------------------------------------------------------------------
| Final Mockery Configuration
|--------------------------------------------------------------------------
*/

// إعدادات إضافية لـ Mockery بعد تهيئة Laravel
if (class_exists('Mockery')) {
    // تعيين مستوى التفصيل للأخطاء
    Mockery::getContainer()->mockery_teardown();

    // إعادة تعيين نهائي
    Mockery::resetContainer();
}

return $app;
