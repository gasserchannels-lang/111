<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PointsService $pointsService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = $this->orderService->getOrderHistory($user, $request->get('limit', 10));

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'payments.paymentMethod']);

        return response()->json([
            'order' => $order,
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'cart_items' => 'required|array',
            'cart_items.*.product_id' => 'required|exists:products,id',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'billing_address' => 'required|array',
        ]);

        $user = $request->user();
        $order = $this->orderService->createOrder(
            $user,
            $request->cart_items,
            [
                'shipping' => $request->shipping_address,
                'billing' => $request->billing_address,
            ]
        );

        // Award points for purchase
        $this->pointsService->awardPurchasePoints($order);

        return response()->json([
            'success' => true,
            'order' => $order,
            'message' => 'تم إنشاء الطلب بنجاح',
        ], 201);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
        ]);

        $oldStatus = $order->status;
        $success = $this->orderService->updateOrderStatus($order, $request->status);

        if ($success) {
            // Send notification
            $order->user->notify(
                new \App\Notifications\OrderStatusUpdateNotification($order, $oldStatus)
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الطلب بنجاح',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'لا يمكن تحديث حالة الطلب',
        ], 400);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $success = $this->orderService->cancelOrder($order, $request->reason);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الطلب بنجاح',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'لا يمكن إلغاء الطلب',
        ], 400);
    }
}
