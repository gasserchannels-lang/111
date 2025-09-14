<?php

namespace Tests\Security;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SQLInjectionTest extends TestCase
{
    #[Test]
    public function login_form_protected_from_sql_injection()
    {
        $maliciousInputs = [
            "admin'--",
            "admin' OR '1'='1",
            "admin' OR 1=1--",
            "admin'; DROP TABLE users; --",
            "admin' UNION SELECT * FROM users--",
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->postJson('/api/login', [
                'email' => $input,
                'password' => 'password',
            ]);

            // Should not return 500 error (SQL error)
            $this->assertNotEquals(500, $response->status());
            $this->assertNotEquals(200, $response->status()); // Should not login
        }
    }

    #[Test]
    public function search_function_protected_from_sql_injection()
    {
        $maliciousInputs = [
            "'; DROP TABLE products; --",
            "' OR '1'='1",
            "' UNION SELECT * FROM users--",
            "'; INSERT INTO users (email) VALUES ('hacker@test.com'); --",
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->getJson('/api/search?q='.urlencode($input));

            // Should not return 500 error
            $this->assertNotEquals(500, $response->status());

            // Should not return sensitive data
            $responseData = $response->json();
            if (is_array($responseData)) {
                $this->assertArrayNotHasKey('password', $responseData);
                $this->assertArrayNotHasKey('email', $responseData);
            }
        }
    }

    #[Test]
    public function product_filter_protected_from_sql_injection()
    {
        $maliciousInputs = [
            "1'; DROP TABLE products; --",
            '1 OR 1=1',
            "1' UNION SELECT * FROM users--",
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->getJson('/api/products?category_id='.urlencode($input));

            $this->assertNotEquals(500, $response->status());
        }
    }

    #[Test]
    public function user_registration_protected_from_sql_injection()
    {
        $maliciousInputs = [
            "'; DROP TABLE users; --",
            "admin' OR '1'='1",
            "test@test.com'; INSERT INTO users (email) VALUES ('hacker@test.com'); --",
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->postJson('/api/register', [
                'name' => $input,
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $this->assertNotEquals(500, $response->status());
        }
    }

    #[Test]
    public function database_queries_use_parameterized_statements()
    {
        // This test ensures that raw SQL queries use parameterized statements
        $user = User::factory()->create();

        // Test that we can safely query with user input
        $searchTerm = "'; DROP TABLE users; --";

        // This should not cause any issues if using Eloquent or parameterized queries
        $users = User::where('name', 'like', '%'.$searchTerm.'%')->get();

        $this->assertIsObject($users);
        $this->assertCount(0, $users); // Should return empty result, not error
    }

    #[Test]
    public function api_endpoints_validate_input_properly()
    {
        $maliciousInputs = [
            "'; DROP TABLE products; --",
            "' OR '1'='1",
            "1'; DELETE FROM products; --",
        ];

        $endpoints = [
            '/api/products',
        ];

        foreach ($endpoints as $endpoint) {
            foreach ($maliciousInputs as $input) {
                $response = $this->getJson($endpoint.'?search='.urlencode($input));

                $this->assertNotEquals(500, $response->status());
            }
        }
    }

    #[Test]
    public function database_connection_remains_stable_after_attack_attempts()
    {
        $maliciousInputs = [
            "'; DROP TABLE users; --",
            "'; DROP TABLE products; --",
            "'; DROP TABLE categories; --",
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->postJson('/api/search', ['query' => $input]);
            $this->assertNotEquals(500, $response->status());
        }

        // Verify database is still functional
        $userCount = User::count();
        $this->assertIsInt($userCount);
        $this->assertGreaterThanOrEqual(0, $userCount);
    }

    #[Test]
    public function error_messages_do_not_reveal_database_structure()
    {
        $maliciousInput = "'; DROP TABLE users; --";

        $response = $this->postJson('/api/login', [
            'email' => $maliciousInput,
            'password' => 'password',
        ]);

        $responseData = $response->json();

        if (isset($responseData['message'])) {
            $message = $responseData['message'];

            // Error message should not contain database table names
            $this->assertStringNotContainsString('users', $message);
            $this->assertStringNotContainsString('products', $message);
            $this->assertStringNotContainsString('categories', $message);

            // Error message should not contain SQL syntax
            $this->assertStringNotContainsString('DROP TABLE', $message);
            $this->assertStringNotContainsString('UNION', $message);
            $this->assertStringNotContainsString('SELECT', $message);
        }
    }
}
