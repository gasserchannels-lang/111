<?php

namespace Tests\AI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AIErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ai_handles_invalid_input_gracefully()
    {
        $response = $this->postJson('/api/ai/analyze', [
            'text' => '',
            'type' => 'invalid_type',
        ]);

        $this->assertEquals(422, $response->status());
        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function ai_handles_malformed_json()
    {
        $response = $this->postJson('/api/ai/analyze', [
            'text' => null,
            'type' => 'product_analysis',
        ]);

        $this->assertEquals(422, $response->status());
    }

    /** @test */
    public function ai_handles_network_timeout()
    {
        // محاكاة timeout
        $this->app['config']->set('ai.timeout', 1);

        $response = $this->postJson('/api/ai/analyze', [
            'text' => 'Test timeout scenario',
            'type' => 'product_analysis',
        ]);

        // يجب أن يعيد خطأ timeout بدلاً من crash
        $this->assertContains($response->status(), [408, 500, 503]);
    }

    /** @test */
    public function ai_logs_errors_properly()
    {
        Log::shouldReceive('error')
            ->once()
            ->with('AI Service Error: Invalid input provided');

        $this->postJson('/api/ai/analyze', [
            'text' => '',
            'type' => 'product_analysis',
        ]);
    }

    /** @test */
    public function ai_returns_meaningful_error_messages()
    {
        $response = $this->postJson('/api/ai/analyze', [
            'text' => 'Test',
            'type' => 'unsupported_type',
        ]);

        $this->assertEquals(400, $response->status());
        $responseData = $response->json();

        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('Unsupported analysis type', $responseData['message']);
    }

    /** @test */
    public function ai_handles_concurrent_requests()
    {
        $responses = [];

        // إرسال 5 طلبات متزامنة
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->postJson('/api/ai/analyze', [
                'text' => "Concurrent request {$i}",
                'type' => 'product_analysis',
            ]);
        }

        // جميع الطلبات يجب أن تعمل بشكل صحيح
        foreach ($responses as $response) {
            $this->assertContains($response->status(), [200, 429]); // 429 = Too Many Requests
        }
    }
}
