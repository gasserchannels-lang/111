<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageOptimizationService
{
    public function optimizeImage(string $path, array $sizes = []): array
    {
        $defaultSizes = [
            'thumbnail' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200],
        ];

        $sizes = array_merge($defaultSizes, $sizes);
        $optimizedImages = [];

        try {
            $originalPath = Storage::path($path);
            $image = Image::make($originalPath);

            foreach ($sizes as $sizeName => $dimensions) {
                $optimizedPath = $this->generateOptimizedPath($path, $sizeName);

                $resizedImage = $image->resize($dimensions[0], $dimensions[1], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Convert to WebP for better compression
                $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $optimizedPath);
                $resizedImage->encode('webp', 85)->save(Storage::path($webpPath));

                $optimizedImages[$sizeName] = [
                    'path' => $webpPath,
                    'url' => Storage::url($webpPath),
                    'width' => $resizedImage->width(),
                    'height' => $resizedImage->height(),
                    'size' => filesize(Storage::path($webpPath)),
                ];
            }

            return $optimizedImages;
        } catch (\Exception $e) {
            Log::error('Image optimization failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function generateResponsiveImages(string $originalPath): string
    {
        $optimizedImages = $this->optimizeImage($originalPath);

        $srcset = collect($optimizedImages)
            ->map(function ($image, $size) {
                return $image['url'].' '.$image['width'].'w';
            })
            ->implode(', ');

        return $srcset;
    }

    public function lazyLoadImage(string $originalPath, string $alt = '', array $attributes = []): string
    {
        $optimizedImages = $this->optimizeImage($originalPath);
        $placeholder = $this->generatePlaceholder($originalPath);

        $attributes = array_merge([
            'class' => 'lazy-load',
            'data-src' => $optimizedImages['medium']['url'] ?? Storage::url($originalPath),
            'data-srcset' => $this->generateResponsiveImages($originalPath),
            'alt' => $alt,
        ], $attributes);

        $attributesString = collect($attributes)
            ->map(fn ($value, $key) => $key.'="'.htmlspecialchars($value).'"')
            ->implode(' ');

        return '<img '.$attributesString.' src="'.$placeholder.'">';
    }

    private function generateOptimizedPath(string $originalPath, string $size): string
    {
        $pathInfo = pathinfo($originalPath);

        return $pathInfo['dirname'].'/'.$pathInfo['filename'].'_'.$size.'.'.$pathInfo['extension'];
    }

    private function generatePlaceholder(string $originalPath): string
    {
        // Generate a low-quality placeholder
        $image = Image::make(Storage::path($originalPath));
        $placeholder = $image->resize(20, 20)->blur(10)->encode('data-url');

        return $placeholder;
    }

    public function compressImage(string $path, int $quality = 85): bool
    {
        try {
            $image = Image::make(Storage::path($path));
            $image->encode(null, $quality)->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Image compression failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
