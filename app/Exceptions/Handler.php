<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // 1. لا تلمس أخطاء التحقق، دع Laravel يعالجها (لإرجاع 422)
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return null; 
            }
        });

        // 2. حول أخطاء قاعدة البيانات إلى 500
        $this->renderable(function (QueryException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'A database error occurred.'], 500);
            }
        });

        // 3. حول أي خطأ عام آخر (بما في ذلك الخطأ الذي يحاكيه الاختبار)
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') && !$this->isHttpException($e)) {
                return response()->json(['message' => 'An unexpected server error occurred.'], 500);
            }
        });
    }
}
