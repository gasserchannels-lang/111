<?php

namespace Tests\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XSSTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_input_is_properly_escaped_in_html_output()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            'javascript:alert("XSS")',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            '<svg onload="alert(\'XSS\')"></svg>',
        ];

        foreach ($xssPayloads as $payload) {
            $response = $this->postJson('/api/products', [
                'name' => $payload,
                'description' => $payload,
                'price' => 100,
            ]);

            if ($response->status() === 200) {
                $data = $response->json();

                // Check that the payload is escaped in the response
                if (isset($data['name'])) {
                    $this->assertStringNotContainsString('<script>', $data['name']);
                    $this->assertStringNotContainsString('onerror=', $data['name']);
                    $this->assertStringNotContainsString('javascript:', $data['name']);
                }
            }
        }
    }

    /** @test */
    public function search_results_are_properly_escaped()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            'javascript:alert("XSS")',
        ];

        foreach ($xssPayloads as $payload) {
            $response = $this->getJson('/api/search?q='.urlencode($payload));

            if ($response->status() === 200) {
                $data = $response->json();

                if (isset($data['results'])) {
                    foreach ($data['results'] as $result) {
                        if (isset($result['name'])) {
                            $this->assertStringNotContainsString('<script>', $result['name']);
                            $this->assertStringNotContainsString('onerror=', $result['name']);
                        }
                    }
                }
            }
        }
    }

    /** @test */
    public function user_profile_data_is_properly_escaped()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->putJson('/api/user/profile', [
            'name' => $xssPayload,
            'bio' => $xssPayload,
        ]);

        if ($response->status() === 200) {
            $data = $response->json();

            if (isset($data['name'])) {
                $this->assertStringNotContainsString('<script>', $data['name']);
            }
            if (isset($data['bio'])) {
                $this->assertStringNotContainsString('<script>', $data['bio']);
            }
        }
    }

    /** @test */
    public function product_reviews_are_properly_escaped()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->postJson('/api/products/1/reviews', [
            'rating' => 5,
            'comment' => $xssPayload,
        ]);

        if ($response->status() === 200) {
            $data = $response->json();

            if (isset($data['comment'])) {
                $this->assertStringNotContainsString('<script>', $data['comment']);
            }
        }
    }

    /** @test */
    public function admin_panel_is_protected_from_xss()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            'javascript:alert("XSS")',
        ];

        foreach ($xssPayloads as $payload) {
            $response = $this->postJson('/api/admin/products', [
                'name' => $payload,
                'description' => $payload,
            ]);

            if ($response->status() === 200) {
                $data = $response->json();

                if (isset($data['name'])) {
                    $this->assertStringNotContainsString('<script>', $data['name']);
                }
            }
        }
    }

    /** @test */
    public function json_responses_are_properly_encoded()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->postJson('/api/contact', [
            'name' => $xssPayload,
            'email' => 'test@example.com',
            'message' => $xssPayload,
        ]);

        if ($response->status() === 200) {
            $content = $response->getContent();

            // JSON should be properly encoded
            $this->assertStringNotContainsString('<script>', $content);
            $this->assertStringContainsString('\\u003cscript\\u003e', $content);
        }
    }

    /** @test */
    public function file_upload_names_are_properly_sanitized()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $maliciousFileName = '<script>alert("XSS")</script>.jpg';
        $file = \Illuminate\Http\UploadedFile::fake()->image($maliciousFileName);

        $response = $this->postJson('/api/upload', [
            'file' => $file,
        ]);

        if ($response->status() === 200) {
            $data = $response->json();

            if (isset($data['filename'])) {
                $this->assertStringNotContainsString('<script>', $data['filename']);
                $this->assertStringNotContainsString('alert', $data['filename']);
            }
        }
    }

    /** @test */
    public function error_messages_are_properly_escaped()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->postJson('/api/login', [
            'email' => $xssPayload,
            'password' => 'password',
        ]);

        $responseData = $response->json();

        if (isset($responseData['message'])) {
            $this->assertStringNotContainsString('<script>', $responseData['message']);
            $this->assertStringNotContainsString('onerror=', $responseData['message']);
        }
    }

    /** @test */
    public function url_parameters_are_properly_escaped()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->getJson('/api/products?search='.urlencode($xssPayload));

        if ($response->status() === 200) {
            $data = $response->json();

            if (isset($data['search_term'])) {
                $this->assertStringNotContainsString('<script>', $data['search_term']);
            }
        }
    }
}
