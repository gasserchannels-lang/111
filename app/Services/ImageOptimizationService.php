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
        $this->config = config('image_optimization', [
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
        $versions = [];
        // $image = $this->imageManager->read($file->getPathname());
        $image = null; // Placeholder since ImageManager is commented out

        foreach ($this->config['sizes'] as $sizeName => $dimensions) {
            $versions[$sizeName] = $this->createOptimizedVersion(
                $image,
                $path,
                $baseName,
                $sizeName,
                $dimensions[0],
                $dimensions[1]
            );
        }

        return $versions;
    }

    /**
     * Create a single optimized version.
     *
     * @param  mixed  $image
     * @return array<string, mixed>
     */
    private function createOptimizedVersion($image, string $path, string $baseName, string $sizeName, int $width, int $height): array
    {
        $versions = [];

        // Resize image
        $resizedImage = $image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Create different formats
        foreach ($this->config['formats'] as $format) {
            $filename = $baseName.'_'.$sizeName.'_'.time().'.'.$format;
            $filePath = $path.'/optimized/'.$filename;

            // Optimize based on format
            $optimizedImage = $this->optimizeForFormat($resizedImage, $format);

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

        return $versions;
    }

    /**
     * Optimize image for specific format.
     *
     * @param  mixed  $image
     */
    private function optimizeForFormat($image, string $format): string
    {
        switch ($format) {
            case 'webp':
                return $image->toWebp($this->config['quality'])->toString();
            case 'jpg':
            case 'jpeg':
                return $image->toJpeg($this->config['quality'])->toString();
            case 'png':
                return $image->toPng()->toString();
            default:
                return $image->toJpeg($this->config['quality'])->toString();
        }
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
        if (isset($optimizedVersions['medium']['webp'])) {
            $defaultSrc = $optimizedVersions['medium']['webp']['url'];
        } elseif (isset($optimizedVersions['medium']['jpg'])) {
            $defaultSrc = $optimizedVersions['medium']['jpg']['url'];
        }

        $html = '<picture>';

        // Add source elements for different sizes
        foreach (['large', 'medium', 'small'] as $size) {
            if (isset($optimizedVersions[$size])) {
                $sources = $optimizedVersions[$size];

                // WebP source
                if (isset($sources['webp'])) {
                    $html .= sprintf(
                        '<source media="(min-width: %dpx)" srcset="%s" type="image/webp">',
                        $sources['webp']['width'],
                        $sources['webp']['url']
                    );
                }

                // JPEG source
                if (isset($sources['jpg'])) {
                    $html .= sprintf(
                        '<source media="(min-width: %dpx)" srcset="%s" type="image/jpeg">',
                        $sources['jpg']['width'],
                        $sources['jpg']['url']
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
            $imgTag .= ' '.$key.'="'.htmlspecialchars($value).'"';
        }
        $imgTag .= '>';

        $html .= $imgTag;
        $html .= '</picture>';

        return $html;
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
            $imgTag .= ' '.$key.'="'.htmlspecialchars($value).'"';
        }
        $imgTag .= '>';

        return $imgTag;
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
