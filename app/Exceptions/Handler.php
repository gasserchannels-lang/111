<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            //
        });

        // ✅ *** هذا هو الإصلاح الكامل والنهائي ***
        // يجب أن يكون ترتيب المعالجات من الأكثر تحديداً إلى الأكثر عمومية

        // 1. التعامل مع أخطاء التحقق (Validation) أولاً
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // 2. التعامل مع أخطاء "غير موجود" (Not Found)
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }
        });

        // 3. التعامل مع أخطاء قاعدة البيانات
        $this->renderable(function (QueryException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'A server-side database error occurred.'], 500);
            }
        });

        // 4. التعامل مع أي خطأ عام آخر كآخر احتمال
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'An unexpected server error occurred.'], 500);
            }
        });
    }
}
