<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class WatermarkService
{
    /**
     * @var array<string, string|int|float|bool>
     */
    private array $config;

    public function __construct()
    {
        $config = config('watermark', [
            'enabled' => true,
            'text' => 'COPRRA',
            'font_size' => 24,
            'font_color' => '#FFFFFF',
            'background_color' => '#000000',
            'opacity' => 0.7,
            'position' => 'bottom-right',
            'margin' => 10,
            'font_family' => 'Arial',
        ]);
        if (is_array($config)) {
            $this->config = array_merge($this->config, array_filter($config, function($value) {
                return is_string($value) || is_numeric($value) || is_bool($value);
            }));
        }
    }

    /**
     * Add watermark to image.
     */
    public function addWatermark(UploadedFile $file, ?string $watermarkText = null): UploadedFile
    {
        try {
            if (! $this->config['enabled']) {
                return $file;
            }

            $watermarkText = (string) ($watermarkText ?? $this->config['text']);

            // Create watermarked image
            $watermarkedPath = $this->createWatermarkedImage($file, $watermarkText);

            // Create new UploadedFile from watermarked image
            $watermarkedFile = new UploadedFile(
                $watermarkedPath,
                $file->getClientOriginalName(),
                $file->getMimeType(),
                null,
                true
            );

            Log::info('Watermark added to image', [
                'original_name' => $file->getClientOriginalName(),
                'watermark_text' => $watermarkText,
            ]);

            return $watermarkedFile;
        } catch (Exception $e) {
            Log::error('Failed to add watermark to image', [
                'error' => $e->getMessage(),
                'original_name' => $file->getClientOriginalName(),
                'watermark_text' => $watermarkText,
            ]);

            // Return original file if watermarking fails
            return $file;
        }
    }

    /**
     * Add watermark to multiple images.
     *
     * @param  array<UploadedFile>  $files
     * @return array<UploadedFile|array<string, mixed>>
     */
    public function addWatermarkToMultiple(array $files, ?string $watermarkText = null): array
    {
        $results = [];

        foreach ($files as $file) {
            try {
                $results[] = $this->addWatermark($file, $watermarkText);
            } catch (Exception $e) {
                $results[] = [
                    'error' => $e->getMessage(),
                    'filename' => $file->getClientOriginalName(),
                ];
            }
        }

        return $results;
    }

    /**
     * Add watermark to existing image in storage.
     */
    public function addWatermarkToStoredImage(string $imagePath, ?string $watermarkText = null): string
    {
        try {
            if (! $this->config['enabled']) {
                return $imagePath;
            }

            $watermarkText = (string) ($watermarkText ?? $this->config['text']);

            // Download image from storage
            $imageContent = Storage::disk('public')->get($imagePath);
            $tempPath = tempnam(sys_get_temp_dir(), 'watermark_');
            file_put_contents($tempPath, $imageContent);

            // Create watermarked image
            $watermarkedPath = $this->createWatermarkedImageFromPath($tempPath, $watermarkText);

            // Upload watermarked image back to storage
            $watermarkedContent = file_get_contents($watermarkedPath);
            if ($watermarkedContent === false) {
                throw new Exception('Failed to read watermarked image content');
            }
            $watermarkedImagePath = str_replace('.', '_watermarked.', $imagePath);
            Storage::disk('public')->put($watermarkedImagePath, $watermarkedContent);

            // Clean up temp files
            unlink($tempPath);
            unlink($watermarkedPath);

            Log::info('Watermark added to stored image', [
                'original_path' => $imagePath,
                'watermarked_path' => $watermarkedImagePath,
                'watermark_text' => $watermarkText,
            ]);

            return $watermarkedImagePath;
        } catch (Exception $e) {
            Log::error('Failed to add watermark to stored image', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
                'watermark_text' => $watermarkText,
            ]);

            return $imagePath;
        }
    }

    /**
     * Create watermarked image from UploadedFile.
     */
    private function createWatermarkedImage(UploadedFile $file, string $watermarkText): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'watermark_');
        $watermarkedPath = tempnam(sys_get_temp_dir(), 'watermarked_');

        // Copy uploaded file to temp location
        copy($file->getPathname(), $tempPath);

        // Create watermarked image
        $this->createWatermarkedImageFromPath($tempPath, $watermarkText, $watermarkedPath);

        // Clean up temp file
        unlink($tempPath);

        return $watermarkedPath;
    }

    /**
     * Create watermarked image from file path.
     */
    private function createWatermarkedImageFromPath(string $imagePath, string $watermarkText, ?string $outputPath = null): string
    {
        $outputPath = $outputPath ?? tempnam(sys_get_temp_dir(), 'watermarked_');

        // Get image info
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            throw new Exception('Failed to get image information');
        }
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create image resource based on type
        $image = $this->createImageResource($imagePath, $mimeType);

        if (! $image) {
            throw new Exception('Failed to create image resource');
        }

        // Add watermark
        $this->drawWatermark($image, $watermarkText, $width, $height);

        // Save watermarked image
        if ($outputPath === false) {
            throw new Exception('Failed to create output path');
        }
        $this->saveImage($image, $outputPath, $mimeType);

        // Clean up image resource
        imagedestroy($image);

        return $outputPath;
    }

    /**
     * Create image resource from file.
     *
     * @return \GdImage|false
     */
    private function createImageResource(string $imagePath, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($imagePath);
            case 'image/png':
                return imagecreatefrompng($imagePath);
            case 'image/gif':
                return imagecreatefromgif($imagePath);
            case 'image/webp':
                return imagecreatefromwebp($imagePath);
            default:
                throw new Exception('Unsupported image type: '.$mimeType);
        }
    }

    /**
     * Draw watermark on image.
     */
    private function drawWatermark(\GdImage $image, string $watermarkText, int $width, int $height): void
    {
        // Calculate watermark position
        $position = $this->calculateWatermarkPosition($width, $height, $watermarkText);

        // Create watermark background
        $watermarkWidth = max(1, $position['width']);
        $watermarkHeight = max(1, $position['height']);
        $watermark = imagecreatetruecolor($watermarkWidth, $watermarkHeight);

        // Set background color with opacity
        $backgroundColor = $this->hexToRgb((string) $this->config['background_color']);
        $bgColor = imagecolorallocatealpha(
            $watermark,
            max(0, min(255, $backgroundColor['r'])),
            max(0, min(255, $backgroundColor['g'])),
            max(0, min(255, $backgroundColor['b'])),
            max(0, min(127, (int) ((1 - (float) $this->config['opacity']) * 127)))
        );
        if ($bgColor !== false) {
            imagefill($watermark, 0, 0, $bgColor);
        }

        // Set text color
        $textColor = $this->hexToRgb((string) $this->config['font_color']);
        $textColorResource = imagecolorallocate(
            $watermark,
            max(0, min(255, $textColor['r'])),
            max(0, min(255, $textColor['g'])),
            max(0, min(255, $textColor['b']))
        );

        // Draw text
        $fontSize = (int) $this->config['font_size'];
        $textX = (int) (($watermarkWidth - strlen($watermarkText) * $fontSize * 0.6) / 2);
        $textY = (int) (($watermarkHeight + $fontSize) / 2);

        if ($textColorResource !== false) {
            imagestring($watermark, 5, $textX, $textY, $watermarkText, $textColorResource);
        }

        // Merge watermark with original image
        imagecopymerge(
            $image,
            $watermark,
            $position['x'],
            $position['y'],
            0,
            0,
            $watermarkWidth,
            $watermarkHeight,
            (int) ((float) $this->config['opacity'] * 100)
        );

        // Clean up watermark resource
        imagedestroy($watermark);
    }

    /**
     * Calculate watermark position.
     *
     * @return array<string, int>
     */
    private function calculateWatermarkPosition(int $width, int $height, string $watermarkText): array
    {
        $fontSize = (int) $this->config['font_size'];
        $margin = (int) $this->config['margin'];

        // Calculate watermark dimensions
        $watermarkWidth = strlen($watermarkText) * $fontSize * 0.6 + $margin * 2;
        $watermarkHeight = $fontSize + $margin * 2;

        // Calculate position based on config
        $position = (string) $this->config['position'];

        switch ($position) {
            case 'top-left':
                $x = $margin;
                $y = $margin;
                break;
            case 'top-right':
                $x = $width - $watermarkWidth - $margin;
                $y = $margin;
                break;
            case 'bottom-left':
                $x = $margin;
                $y = $height - $watermarkHeight - $margin;
                break;
            case 'bottom-right':
            default:
                $x = $width - $watermarkWidth - $margin;
                $y = $height - $watermarkHeight - $margin;
                break;
            case 'center':
                $x = ($width - $watermarkWidth) / 2;
                $y = ($height - $watermarkHeight) / 2;
                break;
        }

        return [
            'x' => (int) max(0, $x),
            'y' => (int) max(0, $y),
            'width' => (int) $watermarkWidth,
            'height' => (int) $watermarkHeight,
        ];
    }

    /**
     * Save image to file.
     */
    private function saveImage(\GdImage $image, string $outputPath, string $mimeType): void
    {
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($image, $outputPath, 90);
                break;
            case 'image/png':
                imagepng($image, $outputPath, 9);
                break;
            case 'image/gif':
                imagegif($image, $outputPath);
                break;
            case 'image/webp':
                imagewebp($image, $outputPath, 90);
                break;
            default:
                throw new Exception('Unsupported output image type: '.$mimeType);
        }
    }

    /**
     * Convert hex color to RGB.
     *
     * @return array<string, int>
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            'r' => (int) hexdec(substr($hex, 0, 2)),
            'g' => (int) hexdec(substr($hex, 2, 2)),
            'b' => (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Get watermark configuration.
     *
     * @return array<string, string|int|float|bool>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Enable watermark.
     */
    public function enable(): void
    {
        $this->config['enabled'] = true;
    }

    /**
     * Disable watermark.
     */
    public function disable(): void
    {
        $this->config['enabled'] = false;
    }

    /**
     * Check if watermark is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'];
    }

    /**
     * Update watermark configuration with validation.
     *
     * @param  array<string, string|int|float|bool>  $config
     */
    public function updateConfig(array $config): void
    {
        if (isset($config['opacity'])) {
            $opacity = (float) $config['opacity'];
            if ($opacity < 0 || $opacity > 1) {
                throw new Exception('Opacity must be between 0 and 1');
            }
            $this->config['opacity'] = $opacity;
        }

        if (isset($config['font_size'])) {
            $fontSize = (int) $config['font_size'];
            if ($fontSize < 8 || $fontSize > 72) {
                throw new Exception('Font size must be between 8 and 72');
            }
            $this->config['font_size'] = $fontSize;
        }

        if (isset($config['font_color'])) {
            $this->config['font_color'] = (string) $config['font_color'];
        }

        if (isset($config['background_color'])) {
            $this->config['background_color'] = (string) $config['background_color'];
        }
    }
}
