<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function processPayment(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_data' => 'required|array',
        ]);

        $payment = $this->paymentService->processPayment(
            $order,
            $request->payment_method_id,
            $request->payment_data
        );

        return response()->json([
            'success' => $payment->status === 'completed',
            'payment' => $payment,
            'message' => $payment->status === 'completed'
                ? 'تم معالجة الدفع بنجاح'
                : 'فشل في معالجة الدفع',
        ]);
    }

    public function getPaymentMethods(): JsonResponse
    {
        $methods = PaymentMethod::active()->get();

        return response()->json([
            'payment_methods' => $methods,
        ]);
    }

    public function refundPayment(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $payment = $order->payments()->where('status', 'completed')->first();

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد دفع مكتمل لهذا الطلب',
            ], 400);
        }

        $success = $this->paymentService->refundPayment(
            $payment,
            $request->amount
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'تم استرداد المبلغ بنجاح' : 'فشل في استرداد المبلغ',
        ]);
    }
}
