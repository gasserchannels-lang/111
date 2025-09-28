<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal;
use Stripe\StripeClient;

class PaymentService
{
    private StripeClient $stripe;

    private PayPal $paypal;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        $this->paypal = new PayPal;
        $this->paypal->setApiCredentials(config('paypal'));
    }

    public function processPayment(Order $order, string $paymentMethodId, array $paymentData): Payment
    {
        $paymentMethod = PaymentMethod::findOrFail($paymentMethodId);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method_id' => $paymentMethodId,
            'transaction_id' => $this->generateTransactionId(),
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'status' => 'processing',
        ]);

        try {
            switch ($paymentMethod->gateway) {
                case 'stripe':
                    $result = $this->processStripePayment($payment, $paymentData);
                    break;
                case 'paypal':
                    $result = $this->processPayPalPayment($payment, $paymentData);
                    break;
                default:
                    throw new \Exception('Unsupported payment gateway');
            }

            $payment->update([
                'status' => $result['status'],
                'gateway_response' => $result['response'],
                'processed_at' => now(),
            ]);

            if ($result['status'] === 'completed') {
                $order->update(['status' => 'processing']);
            }
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            $payment->update([
                'status' => 'failed',
                'gateway_response' => ['error' => $e->getMessage()],
            ]);
        }

        return $payment;
    }

    private function processStripePayment(Payment $payment, array $data): array
    {
        $intent = $this->stripe->paymentIntents->create([
            'amount' => $payment->amount * 100, // Convert to cents
            'currency' => $payment->currency,
            'payment_method' => $data['payment_method_id'],
            'confirmation_method' => 'manual',
            'confirm' => true,
        ]);

        return [
            'status' => $intent->status === 'succeeded' ? 'completed' : 'failed',
            'response' => $intent->toArray(),
        ];
    }

    private function processPayPalPayment(Payment $payment, array $data): array
    {
        $response = $this->paypal->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $payment->currency,
                        'value' => $payment->amount,
                    ],
                ],
            ],
        ]);

        return [
            'status' => $response['status'] === 'COMPLETED' ? 'completed' : 'failed',
            'response' => $response,
        ];
    }

    private function generateTransactionId(): string
    {
        return 'TXN_'.time().'_'.strtoupper(substr(md5(uniqid()), 0, 8));
    }

    public function refundPayment(Payment $payment, ?float $amount = null): bool
    {
        $refundAmount = $amount ?? $payment->amount;

        try {
            switch ($payment->paymentMethod->gateway) {
                case 'stripe':
                    $this->stripe->refunds->create([
                        'payment_intent' => $payment->gateway_response['id'],
                        'amount' => $refundAmount * 100,
                    ]);
                    break;
                case 'paypal':
                    $this->paypal->refundOrder($payment->transaction_id, $refundAmount);
                    break;
            }

            $payment->update(['status' => 'refunded']);

            return true;
        } catch (\Exception $e) {
            Log::error('Refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
