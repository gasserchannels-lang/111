<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormValidationTest extends TestCase
{
    

    #[Test]
    public function validates_required_fields()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function validates_email_format()
    {
        $rules = ['email' => 'email'];

        $validEmails = ['test@example.com', 'user.name@domain.co.uk'];
        $invalidEmails = ['invalid-email', '@domain.com', 'user@'];

        foreach ($validEmails as $email) {
            $validator = Validator::make(['email' => $email], $rules);
            $this->assertTrue($validator->passes(), "Valid email failed: $email");
        }

        foreach ($invalidEmails as $email) {
            $validator = Validator::make(['email' => $email], $rules);
            $this->assertTrue($validator->fails(), "Invalid email passed: $email");
        }
    }

    #[Test]
    public function validates_password_strength()
    {
        $rules = ['password' => 'min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'];

        $strongPasswords = ['Password123', 'MyStr0ng!Pass'];
        $weakPasswords = ['12345678', 'password', 'PASSWORD123'];

        foreach ($strongPasswords as $password) {
            $validator = Validator::make(['password' => $password], $rules);
            $this->assertTrue($validator->passes(), "Strong password failed: $password");
        }

        foreach ($weakPasswords as $password) {
            $validator = Validator::make(['password' => $password], $rules);
            $this->assertTrue($validator->fails(), "Weak password passed: $password");
        }
    }

    #[Test]
    public function validates_unique_constraints()
    {
        $rules = ['email' => 'unique:users,email'];

        // First user should pass
        $validator = Validator::make(['email' => 'unique@example.com'], $rules);
        $this->assertTrue($validator->passes());

        // Create user in database
        \App\Models\User::factory()->create(['email' => 'unique@example.com']);

        // Second user with same email should fail
        $validator = Validator::make(['email' => 'unique@example.com'], $rules);
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function validates_file_uploads()
    {
        $rules = [
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Test valid image upload (skip if GD extension not available)
        if (function_exists('imagecreatetruecolor')) {
            $file = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 100, 100);
            $validator = Validator::make(['image' => $file], $rules);
            $this->assertTrue($validator->passes());
        } else {
            $this->markTestSkipped('GD extension not available');
        }
    }
}
