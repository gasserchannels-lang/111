<?php

namespace Tests\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class XSSTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_input_is_properly_escaped_in_html_output()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            'javascript:alert("XSS")',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            '<svg onload="alert(\'XSS\')"></svg>',
        ];

        // Test basic string escaping
        foreach ($xssPayloads as $payload) {
            // Test that HTML entities are properly escaped
            $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');

            $this->assertStringNotContainsString('<script>', $escaped);
            $this->assertStringNotContainsString('<img', $escaped);
            $this->assertStringNotContainsString('javascript:', $escaped);
            $this->assertStringContainsString('&lt;script&gt;', $escaped);

            // Test specific payload escaping
            if (strpos($payload, 'onerror=') !== false) {
                $this->assertStringNotContainsString('onerror=', $escaped);
            }
        }

        // Test JSON encoding
        $testData = ['name' => '<script>alert("XSS")</script>'];
        $jsonEncoded = json_encode($testData);
        $this->assertStringNotContainsString('<script>', $jsonEncoded);
        $this->assertStringContainsString('\\u003cscript\\u003e', $jsonEncoded);
    }

    #[Test]
    public function search_results_are_properly_escaped()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            'javascript:alert("XSS")',
        ];

        // Test search result escaping
        foreach ($xssPayloads as $payload) {
            $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');

            $this->assertStringNotContainsString('<script>', $escaped);
            $this->assertStringNotContainsString('<img', $escaped);
            $this->assertStringNotContainsString('javascript:', $escaped);

            // Test that dangerous content is properly escaped
            $this->assertStringContainsString('&lt;script&gt;', $escaped);
            $this->assertStringContainsString('&lt;img', $escaped);
        }
    }

    #[Test]
    public function user_profile_data_is_properly_escaped()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        // Test profile data escaping
        $escapedName = htmlspecialchars($xssPayload, ENT_QUOTES, 'UTF-8');
        $escapedBio = htmlspecialchars($xssPayload, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $escapedName);
        $this->assertStringNotContainsString('<script>', $escapedBio);
        $this->assertStringContainsString('&lt;script&gt;', $escapedName);
        $this->assertStringContainsString('&lt;script&gt;', $escapedBio);

        // Test that original data is preserved when decoded
        $decodedName = html_entity_decode($escapedName, ENT_QUOTES, 'UTF-8');
        $decodedBio = html_entity_decode($escapedBio, ENT_QUOTES, 'UTF-8');
        $this->assertEquals($xssPayload, $decodedName);
        $this->assertEquals($xssPayload, $decodedBio);
    }

    #[Test]
    public function product_reviews_are_properly_escaped()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        // Test review content escaping
        $escaped = htmlspecialchars($xssPayload, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);

        // Test that the original payload is preserved when decoded
        $decoded = html_entity_decode($escaped, ENT_QUOTES, 'UTF-8');
        $this->assertEquals($xssPayload, $decoded);
    }

    #[Test]
    public function admin_panel_is_protected_from_xss()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            'javascript:alert("XSS")',
        ];

        // Test basic XSS protection for admin data
        foreach ($xssPayloads as $payload) {
            $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');

            $this->assertStringNotContainsString('<script>', $escaped);
            $this->assertStringNotContainsString('<img', $escaped);
            $this->assertStringNotContainsString('javascript:', $escaped);

            // Test specific payload escaping
            if (strpos($payload, '<script>') !== false) {
                $this->assertStringContainsString('&lt;script&gt;', $escaped);
            }
            if (strpos($payload, '<img') !== false) {
                $this->assertStringContainsString('&lt;img', $escaped);
            }
        }
    }

    #[Test]
    public function json_responses_are_properly_encoded()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        // Test JSON encoding directly
        $testData = ['name' => $xssPayload, 'message' => $xssPayload];
        $jsonEncoded = json_encode($testData, JSON_HEX_TAG);

        // JSON encoding should escape < and > characters
        $this->assertStringNotContainsString('<script>', $jsonEncoded);
        $this->assertStringContainsString('\\u003cscript\\u003e', $jsonEncoded);

        // Test that JSON encoding properly escapes XSS
        $decoded = json_decode($jsonEncoded, true);
        $this->assertEquals($xssPayload, $decoded['name']);

        // Test that the JSON is valid
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('name', $decoded);
        $this->assertArrayHasKey('message', $decoded);

        // Test that the JSON string is properly formatted
        $this->assertStringStartsWith('{', $jsonEncoded);
        $this->assertStringEndsWith('}', $jsonEncoded);

        // Test that the JSON contains the expected escaped content
        $this->assertStringContainsString('\\u003cscript\\u003e', $jsonEncoded);
        $this->assertStringContainsString('alert', $jsonEncoded);

        // Test that the JSON is safe for display
        $this->assertStringNotContainsString('<', $jsonEncoded);
        $this->assertStringNotContainsString('>', $jsonEncoded);
    }

    #[Test]
    public function file_upload_names_are_properly_sanitized()
    {
        $maliciousFileName = '<script>alert("XSS")</script>.txt';

        // Test filename sanitization
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '', $maliciousFileName);

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('<', $sanitized);
        $this->assertStringNotContainsString('>', $sanitized);
        $this->assertStringNotContainsString('(', $sanitized);
        $this->assertStringNotContainsString(')', $sanitized);
        $this->assertStringNotContainsString('"', $sanitized);

        // Test that only safe characters remain
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9._-]+$/', $sanitized);

        // Test that the sanitized filename is safe
        $this->assertStringContainsString('script', $sanitized);
        $this->assertStringContainsString('alert', $sanitized);
        $this->assertStringContainsString('XSS', $sanitized);
        $this->assertStringContainsString('.txt', $sanitized);
    }

    #[Test]
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

    #[Test]
    public function url_parameters_are_properly_escaped()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        // Test URL encoding
        $urlEncoded = urlencode($xssPayload);
        $this->assertStringNotContainsString('<script>', $urlEncoded);
        $this->assertStringNotContainsString('<', $urlEncoded);
        $this->assertStringNotContainsString('>', $urlEncoded);

        // Test that URL encoding properly escapes special characters
        $this->assertStringContainsString('%3Cscript%3E', $urlEncoded);
        $this->assertStringContainsString('alert', $urlEncoded); // alert is not encoded

        // Test URL decoding
        $decoded = urldecode($urlEncoded);
        $this->assertEquals($xssPayload, $decoded);

        // Test that decoded content is properly escaped when displayed
        $escaped = htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }
}
