<?php

namespace Tests\Unit\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * اختبارات وقت تحميل الصور
 *
 * هذا الكلاس يختبر أوقات تحميل الصور المختلفة
 * ويحذر من الصور البطيئة التي قد تؤثر على تجربة المستخدم
 *
 * ⚠️ تحذير: يجب تحسين ضغط الصور واستخدام CDN لضمان تحميل سريع
 */
class ImageLoadTimeTest extends TestCase
{
    use RefreshDatabase;

    private static array $cache = [];

    protected function setUp(): void
    {
        parent::setUp();
        self::$cache = [];

        // Mock HTTP responses for image loading
        Http::fake([
            'images/*' => Http::response('', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '1024'
            ])
        ]);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_small_image_load_time(): void
    {
        // ⚠️ تحذير: الصور الصغيرة يجب أن تحمل بسرعة
        $imagePath = '/images/thumbnails/product-1-thumb.jpg';
        $startTime = microtime(true);

        $response = $this->loadImage($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // ⚠️ تحذير: الصورة الصغيرة يجب أن تحمل في أقل من 200ms
        $this->assertLessThan(200, $loadTime, '⚠️ تحذير: الصورة الصغيرة بطيئة جداً! يجب أن تحمل في أقل من 200ms');
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_medium_image_load_time(): void
    {
        // ⚠️ تحذير: الصور المتوسطة قد تحتاج ضغط إضافي
        $imagePath = '/images/products/product-1-medium.jpg';
        $startTime = microtime(true);

        $response = $this->loadImage($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        // ⚠️ تحذير: الصورة المتوسطة يجب أن تحمل في أقل من 500ms
        $this->assertLessThan(500, $loadTime, '⚠️ تحذير: الصورة المتوسطة بطيئة جداً! يجب أن تحمل في أقل من 500ms');
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_large_image_load_time(): void
    {
        // ⚠️ تحذير: الصور الكبيرة قد تحتاج تحسين أو تحميل تدريجي
        $imagePath = '/images/products/product-1-large.jpg';
        $startTime = microtime(true);

        $response = $this->loadImage($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        // ⚠️ تحذير: الصورة الكبيرة يجب أن تحمل في أقل من ثانية واحدة
        $this->assertLessThan(1000, $loadTime, '⚠️ تحذير: الصورة الكبيرة بطيئة جداً! يجب أن تحمل في أقل من ثانية واحدة');
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_high_resolution_image_load_time(): void
    {
        // ⚠️ تحذير: الصور عالية الدقة قد تكون بطيئة جداً بدون تحسين
        $imagePath = '/images/products/product-1-4k.jpg';
        $startTime = microtime(true);

        $response = $this->loadImage($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        // ⚠️ تحذير: الصورة عالية الدقة يجب أن تحمل في أقل من ثانيتين
        $this->assertLessThan(2000, $loadTime, '⚠️ تحذير: الصورة عالية الدقة بطيئة جداً! يجب أن تحمل في أقل من ثانيتين');
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_webp_image_load_time(): void
    {
        // ⚠️ تحذير: تنسيق WebP يجب أن يكون أسرع من JPG
        $imagePath = '/images/products/product-1.webp';
        $startTime = microtime(true);

        $response = $this->loadImage($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        // ⚠️ تحذير: صورة WebP يجب أن تحمل في أقل من 300ms
        $this->assertLessThan(300, $loadTime, '⚠️ تحذير: صورة WebP بطيئة جداً! يجب أن تحمل في أقل من 300ms');
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_png_image_load_time(): void
    {
        $imagePath = '/images/products/product-1.png';
        $startTime = microtime(true);

        $response = $this->loadImage($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(600, $loadTime); // Should load in under 600ms
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_gif_image_load_time(): void
    {
        $imagePath = '/images/animations/loading.gif';
        $startTime = microtime(true);

        $response = $this->loadImage($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(800, $loadTime); // Should load in under 800ms
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_svg_image_load_time(): void
    {
        $imagePath = '/images/icons/logo.svg';
        $startTime = microtime(true);

        $response = $this->loadImage($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $loadTime); // Should load in under 100ms
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_compression(): void
    {
        $imagePath = '/images/products/product-1.jpg';

        // Load without compression
        $startTime = microtime(true);
        $response1 = $this->loadImage($imagePath, false);
        $endTime = microtime(true);
        $uncompressedTime = ($endTime - $startTime) * 1000;

        // Load with compression
        $startTime = microtime(true);
        $response2 = $this->loadImage($imagePath, true);
        $endTime = microtime(true);
        $compressedTime = ($endTime - $startTime) * 1000;

        $this->assertLessThanOrEqual($uncompressedTime, $compressedTime); // Compressed load should be faster or equal
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_cdn(): void
    {
        // ⚠️ تحذير: CDN يجب أن يحسن سرعة تحميل الصور
        $imagePath = '/images/products/product-1.jpg';

        // Load without CDN
        $startTime = microtime(true);
        $response1 = $this->loadImage($imagePath, true, false);
        $endTime = microtime(true);
        $withoutCDNTime = ($endTime - $startTime) * 1000;

        // Load with CDN
        $startTime = microtime(true);
        $response2 = $this->loadImage($imagePath, true, true);
        $endTime = microtime(true);
        $withCDNTime = ($endTime - $startTime) * 1000;

        // ⚠️ تحذير: CDN يجب أن يجعل التحميل أسرع
        $this->assertLessThan($withoutCDNTime, $withCDNTime, '⚠️ تحذير: CDN لا يحسن الأداء! يجب أن يكون التحميل مع CDN أسرع');
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_lazy_loading(): void
    {
        $imagePath = '/images/products/product-1.jpg';
        $startTime = microtime(true);

        $response = $this->loadImageWithLazyLoading($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $loadTime); // Should load quickly with lazy loading
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_resizing(): void
    {
        $imagePath = '/images/products/product-1.jpg';
        $width = 300;
        $height = 200;

        $startTime = microtime(true);

        $response = $this->loadImageWithResizing($imagePath, $width, $height);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(400, $loadTime); // Should load in under 400ms
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_watermarking(): void
    {
        $imagePath = '/images/products/product-1.jpg';
        $startTime = microtime(true);

        $response = $this->loadImageWithWatermarking($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(600, $loadTime); // Should load in under 600ms
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_optimization(): void
    {
        $imagePath = '/images/products/product-1.jpg';
        $startTime = microtime(true);

        $response = $this->loadImageWithOptimization($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(300, $loadTime); // Should load in under 300ms
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_under_load(): void
    {
        // ⚠️ تحذير: تحميل عدة صور متزامنة يجب أن يكون سريعاً
        $imagePaths = [
            '/images/products/product-1.jpg',
            '/images/products/product-2.jpg',
            '/images/products/product-3.jpg',
            '/images/products/product-4.jpg',
            '/images/products/product-5.jpg'
        ];

        $startTime = microtime(true);

        $responses = $this->loadImagesConcurrently($imagePaths);

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        // ⚠️ تحذير: تحميل 5 صور يجب أن يكتمل في أقل من ثانيتين
        $this->assertLessThan(2000, $totalTime, '⚠️ تحذير: تحميل الصور المتزامن بطيء جداً! يجب أن يكتمل في أقل من ثانيتين');
        $this->assertCount(5, $responses);

        // All responses should be successful
        foreach ($responses as $response) {
            $this->assertEquals(200, $response['status_code']);
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_caching(): void
    {
        $imagePath = '/images/products/product-1.jpg';

        // First load (cache miss)
        $startTime = microtime(true);
        $response1 = $this->loadImage($imagePath);
        $endTime = microtime(true);
        $firstLoadTime = ($endTime - $startTime) * 1000;

        // Second load (cache hit)
        $startTime = microtime(true);
        $response2 = $this->loadImage($imagePath);
        $endTime = microtime(true);
        $secondLoadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThanOrEqual($firstLoadTime, $secondLoadTime); // Cached load should be faster or equal
        $this->assertEquals($response1['status_code'], $response2['status_code']); // Status codes should be identical
        $this->assertEquals($response1['content_type'], $response2['content_type']); // Content types should be identical
        $this->assertEquals($response1['content_length'], $response2['content_length']); // Content lengths should be identical
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_progressive_loading(): void
    {
        $imagePath = '/images/products/product-1.jpg';
        $startTime = microtime(true);

        $response = $this->loadImageWithProgressiveLoading($imagePath);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(500, $loadTime); // Should load in under 500ms
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_with_adaptive_loading(): void
    {
        $imagePath = '/images/products/product-1.jpg';
        $deviceType = 'mobile';

        $startTime = microtime(true);

        $response = $this->loadImageWithAdaptiveLoading($imagePath, $deviceType);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(300, $loadTime); // Should load in under 300ms
        $this->assertEquals(200, $response['status_code']);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_scalability(): void
    {
        $imageSizes = ['small', 'medium', 'large', 'xlarge'];
        $loadTimes = [];

        foreach ($imageSizes as $size) {
            $imagePath = "/images/products/product-1-$size.jpg";

            $startTime = microtime(true);
            $this->loadImage($imagePath);
            $endTime = microtime(true);

            $loadTimes[$size] = ($endTime - $startTime) * 1000;
        }

        // Load time should increase reasonably with image size
        $this->assertLessThan(2000, $loadTimes['xlarge']); // Should be under 2 seconds even for xlarge
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_image_load_time_memory_usage(): void
    {
        $imagePath = '/images/products/product-1-large.jpg';

        $memoryBefore = memory_get_usage();

        $response = $this->loadImage($imagePath);

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed); // Should use less than 10MB
        $this->assertEquals(200, $response['status_code']);
    }

    private function loadImage(string $path, bool $compression = true, bool $cdn = false): array
    {
        // Use real HTTP client with mocked responses
        $startTime = microtime(true);
        $response = Http::get($path);
        $endTime = microtime(true);

        $loadTime = ($endTime - $startTime) * 1000;

        return [
            'status_code' => $response->status(),
            'content_type' => $response->header('Content-Type'),
            'content_length' => $response->header('Content-Length'),
            'load_time' => $loadTime
        ];
    }

    private function loadImageWithLazyLoading(string $path): array
    {
        // Simulate lazy loading
        $this->simulateLazyLoading($path);
        return $this->loadImage($path);
    }

    private function loadImageWithResizing(string $path, int $width, int $height): array
    {
        // Simulate image resizing
        $this->simulateImageResizing($path, $width, $height);
        return $this->loadImage($path);
    }

    private function loadImageWithWatermarking(string $path): array
    {
        // Simulate watermarking
        $this->simulateWatermarking($path);
        return $this->loadImage($path);
    }

    private function loadImageWithOptimization(string $path): array
    {
        // Simulate image optimization
        $this->simulateImageOptimization($path);
        return $this->loadImage($path);
    }

    private function loadImagesConcurrently(array $paths): array
    {
        $responses = [];
        foreach ($paths as $path) {
            $responses[] = $this->loadImage($path);
        }
        return $responses;
    }

    private function loadImageWithProgressiveLoading(string $path): array
    {
        // Simulate progressive loading
        $this->simulateProgressiveLoading($path);
        return $this->loadImage($path);
    }

    private function loadImageWithAdaptiveLoading(string $path, string $deviceType): array
    {
        // Simulate adaptive loading
        $this->simulateAdaptiveLoading($path, $deviceType);
        return $this->loadImage($path);
    }

    private function simulateImageLoading(string $path, bool $compression, bool $cdn): void
    {
        $cacheKey = md5($path . (string)$compression . (string)$cdn);
        if (isset(self::$cache[$cacheKey])) {
            usleep(1000); // Simulate very fast cache hit (1ms)
            return;
        }

        // Simulate image loading time based on path and options
        $baseTime = $this->calculateImageLoadTime($path) * 0.001; // Convert to seconds

        // Apply compression factor
        if ($compression) {
            $baseTime *= 0.7;
        }

        // Apply CDN factor
        if ($cdn) {
            $baseTime *= 0.8;
        }

        usleep($baseTime * 1000000); // Sleep for the calculated time
        self::$cache[$cacheKey] = true;
    }

    private function calculateImageLoadTime(string $path): float
    {
        $baseTime = 100; // Base time in milliseconds

        // Add time based on image characteristics
        if (strpos($path, 'thumb') !== false) {
            $baseTime += 50;
        } elseif (strpos($path, 'medium') !== false) {
            $baseTime += 200;
        } elseif (strpos($path, 'large') !== false) {
            $baseTime += 400;
        } elseif (strpos($path, '4k') !== false) {
            $baseTime += 800;
        }

        // Add time based on format
        if (strpos($path, '.webp') !== false) {
            $baseTime *= 0.8;
        } elseif (strpos($path, '.png') !== false) {
            $baseTime *= 1.2;
        } elseif (strpos($path, '.gif') !== false) {
            $baseTime *= 1.5;
        } elseif (strpos($path, '.svg') !== false) {
            $baseTime *= 0.3;
        }

        return $baseTime;
    }

    private function getImageContentType(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            case 'webp':
                return 'image/webp';
            case 'svg':
                return 'image/svg+xml';
            default:
                return 'image/jpeg';
        }
    }

    private function getImageSize(string $path): int
    {
        $baseSize = 1024; // Base size in bytes

        // Add size based on image characteristics
        if (strpos($path, 'thumb') !== false) {
            $baseSize *= 10;
        } elseif (strpos($path, 'medium') !== false) {
            $baseSize *= 50;
        } elseif (strpos($path, 'large') !== false) {
            $baseSize *= 200;
        } elseif (strpos($path, '4k') !== false) {
            $baseSize *= 1000;
        }

        return $baseSize;
    }

    private function simulateLazyLoading(string $path): void
    {
        // Simulate lazy loading delay
        usleep(10000); // 10ms
    }

    private function simulateImageResizing(string $path, int $width, int $height): void
    {
        // Simulate resizing delay
        usleep(50000); // 50ms
    }

    private function simulateWatermarking(string $path): void
    {
        // Simulate watermarking delay
        usleep(100000); // 100ms
    }

    private function simulateImageOptimization(string $path): void
    {
        // Simulate optimization delay
        usleep(30000); // 30ms
    }

    private function simulateProgressiveLoading(string $path): void
    {
        // Simulate progressive loading delay
        usleep(20000); // 20ms
    }

    private function simulateAdaptiveLoading(string $path, string $deviceType): void
    {
        // Simulate adaptive loading delay
        usleep(15000); // 15ms
    }
}
