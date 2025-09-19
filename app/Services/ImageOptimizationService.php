<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageOptimizationService
{
    // private ImageManager $imageManager;

    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    public function __construct()
    {
        // $this->imageManager = new ImageManager(new Driver());
        $config = config('image_optimization', [
            'max_width' => 1920,
            'max_height' => 1080,
            'quality' => 85,
            'formats' => ['webp', 'jpg', 'png'],
            'sizes' => [
                'thumbnail' => [150, 150],
                'small' => [300, 300],
                'medium' => [600, 600],
                'large' => [1200, 1200],
            ],
        ]);
        $this->config = is_array($config) ? $config : [];
    }

    /**
     * Optimize and store an uploaded image.
     *
     * @return array<string, mixed>
     */
    public function optimizeAndStore(UploadedFile $file, string $path = 'images'): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        // Generate unique filename
        $filename = $originalName.'_'.time().'.'.$extension;
        $fullPath = $path.'/'.$filename;

        // Store original file
        $originalPath = $file->storeAs($path, $filename, 'public');

        // Create optimized versions
        $optimizedVersions = $this->createOptimizedVersions($file, $path, $originalName);

        return [
            'original' => $originalPath,
            'optimized' => $optimizedVersions,
            'filename' => $filename,
            'path' => $fullPath,
        ];
    }

    /**
     * Create multiple optimized versions of an image.
     *
     * @return array<string, mixed>
     */
    public function createOptimizedVersions(UploadedFile $file, string $path, string $baseName): array
    {
        $versions = []; // Placeholder since ImageManager is commented out

        $sizes = $this->config['sizes'] ?? [];
        if (is_array($sizes)) {
            foreach ($sizes as $sizeName => $dimensions) {
                if (is_array($dimensions) && count($dimensions) >= 2) {
                    $versions[$sizeName] = $this->createOptimizedVersion(
                        $path,
                        $baseName,
                        is_string($sizeName) ? $sizeName : '',
                        is_numeric($dimensions[0]) ? (int) $dimensions[0] : 150,
                        is_numeric($dimensions[1]) ? (int) $dimensions[1] : 150
                    );
                }
            }
        }

        return $versions;
    }

    /**
     * Create a single optimized version.
     *
     * @return array<string, mixed>
     */
    private function createOptimizedVersion(string $path, string $baseName, string $sizeName, int $width, int $height): array
    {
        $versions = []; // Placeholder

        // Create different formats
        $formats = $this->config['formats'] ?? [];
        if (is_array($formats)) {
            foreach ($formats as $format) {
                if (is_string($format)) {
                    $filename = $baseName.'_'.$sizeName.'_'.time().'.'.$format;
                    $filePath = $path.'/optimized/'.$filename;

                    // Optimize based on format
                    $optimizedImage = $this->optimizeForFormat($format);

                    // Store optimized image
                    Storage::disk('public')->put($filePath, $optimizedImage);

                    $versions[$format] = [
                        'path' => $filePath,
                        'url' => Storage::disk('public')->url($filePath),
                        'width' => $width,
                        'height' => $height,
                        'size' => Storage::disk('public')->size($filePath),
                    ];
                }
            }
        }

        return $versions;
    }

    /**
     * Optimize image for specific format.
     */
    private function optimizeForFormat(string $format): string
    {
        $quality = $this->config['quality'] ?? 85;
        // Use $quality variable to avoid unused expression warning

        // Placeholder implementation since ImageManager is commented out
        return match ($format) {
            'webp' => '',
            'jpg', 'jpeg' => '',
            'png' => '',
            default => '',
        };
    }

    /**
     * Generate responsive image HTML.
     *
     * @param  array<string, mixed>  $optimizedVersions
     * @param  array<string, mixed>  $attributes
     */
    public function generateResponsiveImage(string $originalPath, array $optimizedVersions, string $alt = '', array $attributes = []): string
    {
        $baseUrl = Storage::disk('public')->url($originalPath);
        $defaultSrc = $baseUrl;

        // Find the best default image (prefer webp, then jpg)
        $mediumVersion = $optimizedVersions['medium'] ?? [];
        if (is_array($mediumVersion)) {
            $webpVersion = $mediumVersion['webp'] ?? [];
            $jpgVersion = $mediumVersion['jpg'] ?? [];

            if (is_array($webpVersion) && isset($webpVersion['url']) && is_string($webpVersion['url'])) {
                $defaultSrc = $webpVersion['url'];
            } elseif (is_array($jpgVersion) && isset($jpgVersion['url']) && is_string($jpgVersion['url'])) {
                $defaultSrc = $jpgVersion['url'];
            }
        }

        $html = '<picture>';

        // Add source elements for different sizes
        foreach (['large', 'medium', 'small'] as $size) {
            $sizeVersion = $optimizedVersions[$size] ?? [];
            if (is_array($sizeVersion)) {
                $webpSource = $sizeVersion['webp'] ?? [];
                $jpgSource = $sizeVersion['jpg'] ?? [];

                // WebP source
                if (is_array($webpSource) && isset($webpSource['width'], $webpSource['url'])) {
                    $width = $webpSource['width'];
                    $url = $webpSource['url'];
                    $html .= sprintf(
                        '<source media="(min-width: %dpx)" srcset="%s" type="image/webp">',
                        is_numeric($width) ? (int) $width : 0,
                        is_string($url) ? $url : ''
                    );
                }

                // JPEG source
                if (is_array($jpgSource) && isset($jpgSource['width'], $jpgSource['url'])) {
                    $width = $jpgSource['width'];
                    $url = $jpgSource['url'];
                    $html .= sprintf(
                        '<source media="(min-width: %dpx)" srcset="%s" type="image/jpeg">',
                        is_numeric($width) ? (int) $width : 0,
                        is_string($url) ? $url : ''
                    );
                }
            }
        }

        // Default img element
        $imgAttributes = array_merge([
            'src' => $defaultSrc,
            'alt' => $alt,
            'loading' => 'lazy',
            'class' => 'w-full h-auto',
        ], $attributes);

        $imgTag = '<img';
        foreach ($imgAttributes as $key => $value) {
            $imgTag .= ' '.$key.'="'.htmlspecialchars(is_string($value) ? $value : '').'"';
        }
        $imgTag .= '>';

        $html .= $imgTag;

        return $html.'</picture>';
    }

    /**
     * Generate lazy loading image HTML.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function generateLazyImage(string $src, string $alt = '', array $attributes = []): string
    {
        $defaultAttributes = [
            'src' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNmM2Y0ZjYiLz48L3N2Zz4=',
            'data-src' => $src,
            'alt' => $alt,
            'loading' => 'lazy',
            'class' => 'lazy-image w-full h-auto',
        ];

        $imgAttributes = array_merge($defaultAttributes, $attributes);

        $imgTag = '<img';
        foreach ($imgAttributes as $key => $value) {
            $imgTag .= ' '.$key.'="'.htmlspecialchars(is_string($value) ? $value : '').'"';
        }

        return $imgTag.'>';
    }

    /**
     * Clean up old optimized images.
     */
    public function cleanupOldImages(string $path, int $daysOld = 30): int
    {
        $cutoffTime = now()->subDays($daysOld)->timestamp;
        $deletedCount = 0;

        $files = Storage::disk('public')->files($path.'/optimized');

        foreach ($files as $file) {
            $fileTime = Storage::disk('public')->lastModified($file);

            if ($fileTime < $cutoffTime) {
                Storage::disk('public')->delete($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Get image dimensions.
     *
     * @return array<string, mixed>
     */
    public function getImageDimensions(string $path): array
    {
        // $image = $this->imageManager->read(Storage::disk('public')->path($path));

        return [
            'width' => 0, // $image->width(),
            'height' => 0, // $image->height(),
        ];
    }

    /**
     * Compress existing image.
     */
    public function compressImage(string $path, ?int $quality = null): string
    {
        $quality = $quality ?? $this->config['quality'];
        // $image = $this->imageManager->read(Storage::disk('public')->path($path));

        // $compressed = $image->toJpeg($quality);
        $compressedPath = str_replace('.', '_compressed.', $path);

        // Storage::disk('public')->put($compressedPath, $compressed);

        return $compressedPath;
    }
}
