<?php

namespace Tests\AI;

use App\Services\ImageProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageProcessingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_analyze_product_images()
    {
        $imageProcessor = new ImageProcessingService;

        // Create a test image
        $image = imagecreate(200, 200);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        imagestring($image, 5, 50, 100, 'Product', $black);

        $imagePath = storage_path('app/test-product.jpg');
        imagejpeg($image, $imagePath);
        imagedestroy($image);

        $result = $imageProcessor->analyzeProductImage($imagePath);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('objects', $result);
        $this->assertArrayHasKey('tags', $result);
        $this->assertArrayHasKey('colors', $result);
    }

    #[Test]
    public function can_detect_objects_in_images()
    {
        $imageProcessor = new ImageProcessingService;

        $image = imagecreate(300, 300);
        $white = imagecolorallocate($image, 255, 255, 255);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $white);
        imagefilledellipse($image, 150, 150, 100, 100, $red);

        $imagePath = storage_path('app/test-object.jpg');
        imagejpeg($image, $imagePath);
        imagedestroy($image);

        $objects = $imageProcessor->detectObjects($imagePath);

        $this->assertIsArray($objects);
    }

    #[Test]
    public function can_extract_colors_from_images()
    {
        $imageProcessor = new ImageProcessingService;

        $image = imagecreate(100, 100);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $red);

        $imagePath = storage_path('app/test-color.jpg');
        imagejpeg($image, $imagePath);
        imagedestroy($image);

        $colors = $imageProcessor->extractColors($imagePath);

        $this->assertIsArray($colors);
        $this->assertArrayHasKey('dominant', $colors);
        $this->assertArrayHasKey('palette', $colors);
    }

    #[Test]
    public function can_generate_image_tags()
    {
        $imageProcessor = new ImageProcessingService;

        $image = imagecreate(200, 200);
        $white = imagecolorallocate($image, 255, 255, 255);
        $green = imagecolorallocate($image, 0, 255, 0);
        imagefill($image, 0, 0, $white);
        imagefilledrectangle($image, 50, 50, 150, 150, $green);

        $imagePath = storage_path('app/test-tags.jpg');
        imagejpeg($image, $imagePath);
        imagedestroy($image);

        $tags = $imageProcessor->generateTags($imagePath);

        $this->assertIsArray($tags);
        $this->assertGreaterThan(0, count($tags));
    }

    #[Test]
    public function can_resize_images()
    {
        $imageProcessor = new ImageProcessingService;

        $image = imagecreate(400, 400);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        $originalPath = storage_path('app/test-original.jpg');
        $resizedPath = storage_path('app/test-resized.jpg');
        imagejpeg($image, $originalPath);
        imagedestroy($image);

        $imageProcessor->resizeImage($originalPath, $resizedPath, 200, 200);

        $this->assertFileExists($resizedPath);

        $imageInfo = getimagesize($resizedPath);
        $this->assertEquals(200, $imageInfo[0]);
        $this->assertEquals(200, $imageInfo[1]);
    }

    #[Test]
    public function can_compress_images()
    {
        $imageProcessor = new ImageProcessingService;

        $image = imagecreate(300, 300);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        $originalPath = storage_path('app/test-compress-original.jpg');
        $compressedPath = storage_path('app/test-compress-compressed.jpg');
        imagejpeg($image, $originalPath, 100);
        imagedestroy($image);

        $imageProcessor->compressImage($originalPath, $compressedPath, 80);

        $this->assertFileExists($compressedPath);
        $this->assertLessThan(filesize($originalPath), filesize($compressedPath));
    }

    #[Test]
    public function can_detect_image_quality()
    {
        $imageProcessor = new ImageProcessingService;

        // Create high quality image
        $image = imagecreate(500, 500);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        $imagePath = storage_path('app/test-quality.jpg');
        imagejpeg($image, $imagePath, 95);
        imagedestroy($image);

        $quality = $imageProcessor->detectQuality($imagePath);

        $this->assertIsFloat($quality);
        $this->assertGreaterThan(0, $quality);
        $this->assertLessThanOrEqual(1, $quality);
    }

    #[Test]
    public function can_handle_multiple_image_formats()
    {
        $imageProcessor = new ImageProcessingService;

        $formats = ['jpg', 'png', 'gif'];

        foreach ($formats as $format) {
            $image = imagecreate(100, 100);
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);

            $imagePath = storage_path("app/test-format.{$format}");

            switch ($format) {
                case 'jpg':
                    imagejpeg($image, $imagePath);
                    break;
                case 'png':
                    imagepng($image, $imagePath);
                    break;
                case 'gif':
                    imagegif($image, $imagePath);
                    break;
            }

            imagedestroy($image);

            $result = $imageProcessor->analyzeProductImage($imagePath);
            $this->assertIsArray($result);
        }
    }
}
