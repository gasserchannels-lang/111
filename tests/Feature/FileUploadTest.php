<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_upload_image_file()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('product.jpg', 100, 100);

        $response = $this->postJson('/api/upload', [
            'file' => $file,
            'type' => 'image',
        ]);

        $response->assertStatus(200);
        Storage::disk('public')->assertExists('uploads/'.$file->hashName());
    }

    /** @test */
    public function validates_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->postJson('/api/upload', [
            'file' => $file,
            'type' => 'image',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function validates_file_size()
    {
        $file = UploadedFile::fake()->image('large.jpg')->size(5000); // 5MB

        $response = $this->postJson('/api/upload', [
            'file' => $file,
            'type' => 'image',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function can_delete_uploaded_file()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $fileName = $file->hashName();

        Storage::disk('public')->put('uploads/'.$fileName, $file->getContent());

        $response = $this->deleteJson('/api/upload/'.$fileName);

        $response->assertStatus(200);
        Storage::disk('public')->assertMissing('uploads/'.$fileName);
    }

    /** @test */
    public function can_upload_multiple_files()
    {
        Storage::fake('public');

        $files = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.jpg'),
            UploadedFile::fake()->image('image3.jpg'),
        ];

        $response = $this->postJson('/api/upload-multiple', [
            'files' => $files,
        ]);

        $response->assertStatus(200);

        foreach ($files as $file) {
            Storage::disk('public')->assertExists('uploads/'.$file->hashName());
        }
    }

    /** @test */
    public function generates_unique_filename()
    {
        Storage::fake('public');

        $file1 = UploadedFile::fake()->image('test.jpg');
        $file2 = UploadedFile::fake()->image('test.jpg');

        $response1 = $this->postJson('/api/upload', ['file' => $file1]);
        $response2 = $this->postJson('/api/upload', ['file' => $file2]);

        $filename1 = $response1->json('filename');
        $filename2 = $response2->json('filename');

        $this->assertNotEquals($filename1, $filename2);
    }
}
