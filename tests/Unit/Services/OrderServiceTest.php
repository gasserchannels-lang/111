<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for the OrderService.
 *
 * @covers \App\Services\OrderService
 */

/**
 * @runTestsInSeparateProcesses
 */
class OrderServiceTest extends TestCase
{
    private OrderService $service;

    private User $user;

    private array $cartItems;

    private array $addresses;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderService;
        $this->user = Mockery::mock(User::class);
        $this->user->id = 1;
        $this->cartItems = [
            [
                'product_id' => 1,
                'quantity'   => 2,
            ],
        ];
        $this->addresses = [
            'shipping' => ['address' => 'Shipping Address'],
            'billing'  => ['address' => 'Billing Address'],
        ];
    }

    /**
     * Test createOrder creates order and items successfully.
     */
    public function test_create_order_successfully(): void
    {
        $product = Mockery::mock(Product::class);
        $product->id = 1;
        $product->price = 10.00;
        $product->name = 'Test Product';
        $product->sku = 'SKU123';
        $product->image = 'image.jpg';
        $product->shouldReceive('findOrFail')->andReturn($product);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('create')->andReturn($order);
        $order->subtotal = 20.00;
        $order->tax_amount = 2.00;
        $order->shipping_amount = 10.00;
        $order->shouldReceive('update')->once()->with(['total_amount' => 32.00])->andReturn(true);

        $orderItem = Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive('create')->once()->andReturn($orderItem);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $result = $this->service->createOrder($this->user, $this->cartItems, $this->addresses);

        $this->assertInstanceOf(Order::class, $result);
    }

    /**
     * Test updateOrderStatus for valid transition.
     */
    public function test_update_order_status_valid_transition(): void
    {
        $order = Mockery::mock(Order::class);
        $order->status = 'pending';
        $order->shouldReceive('update')->once()->with([
            'status' => 'processing',
        ])->andReturn(true);

        $result = $this->service->updateOrderStatus($order, 'processing');

        $this->assertTrue($result);
    }

    /**
     * Test updateOrderStatus for invalid transition.
     */
    public function test_update_order_status_invalid_transition(): void
    {
        $order = Mockery::mock(Order::class);
        $order->status = 'delivered';

        $result = $this->service->updateOrderStatus($order, 'processing');

        $this->assertFalse($result);
    }

    /**
     * Test updateOrderStatus sets shipped_at for shipped status.
     */
    public function test_update_order_status_shipped_sets_timestamp(): void
    {
        $order = Mockery::mock(Order::class);
        $order->status = 'processing';
        $order->shouldReceive('update')->once()->with([
            'status'     => 'shipped',
            'shipped_at' => Mockery::type('Carbon\Carbon'),
        ])->andReturn(true);

        $result = $this->service->updateOrderStatus($order, 'shipped');

        $this->assertTrue($result);
    }

    /**
     * Test cancelOrder for eligible status.
     */
    public function test_cancel_order_eligible(): void
    {
        $order = Mockery::mock(Order::class);
        $order->status = 'pending';
        $order->notes = '';
        $order->shouldReceive('update')->once()->with([
            'status' => 'cancelled',
            'notes'  => 'Cancelled: Test reason',
        ])->andReturn(true);

        $item = Mockery::mock(OrderItem::class);
        $item->quantity = 2;
        $order->items = [$item];

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('increment')->once()->with('stock', 2);

        $item->product = $product;

        $result = $this->service->cancelOrder($order, 'Test reason');

        $this->assertTrue($result);
    }

    /**
     * Test cancelOrder for ineligible status.
     */
    public function test_cancel_order_ineligible(): void
    {
        $order = Mockery::mock(Order::class);
        $order->status = 'delivered';

        $result = $this->service->cancelOrder($order);

        $this->assertFalse($result);
    }

    /**
     * Test getOrderHistory returns orders with relations.
     */
    public function test_get_order_history(): void
    {
        $orders = Mockery::mock(\Illuminate\Database\Eloquent\Collection::class);
        $this->user->shouldReceive('orders')
            ->once()
            ->with(['items.product', 'payments'])
            ->andReturn($orders->shouldReceive('orderBy->limit->get')->andReturn($orders));

        $result = $this->service->getOrderHistory($this->user, 5);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
