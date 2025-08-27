<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException; // ✅ استيراد الكلاس المطلوب
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // ✅ *** هذا هو الجزء الجديد والحاسم ***
        // التعامل مع أخطاء قاعدة البيانات كـ 500 للـ API
        $this->renderable(function (QueryException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Internal Server Error. A database error occurred.',
                ], 500);
            }
        });

        // ✅ *** وهذا الجزء أيضاً مهم جداً ***
        // للتعامل مع الأخطاء العامة التي يحاكيها الاختبار
        $this->renderable(function (\Exception $e, $request) {
            if ($request->is('api/*')) {
                // يمكنك إضافة تسجيل للخطأ هنا إذا أردت
                // \Log::error($e->getMessage());
                return response()->json([
                    'message' => 'An unexpected error occurred.',
                ], 500);
            }
        });
    }
}
