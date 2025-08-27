<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

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
    }
}
