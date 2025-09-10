<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WatermarkService
{
    private array $config;

    private CloudStorageService $cloudStorage;

    public function __construct(CloudStorageService $cloudStorage)
    {
        $this->cloudStorage = $cloudStorage;
        $this->config = config('watermark', [
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

            $watermarkText = $watermarkText ?? $this->config['text'];

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

            $watermarkText = $watermarkText ?? $this->config['text'];

            // Download image from storage
            $imageContent = Storage::disk('public')->get($imagePath);
            $tempPath = tempnam(sys_get_temp_dir(), 'watermark_');
            file_put_contents($tempPath, $imageContent);

            // Create watermarked image
            $watermarkedPath = $this->createWatermarkedImageFromPath($tempPath, $watermarkText);

            // Upload watermarked image back to storage
            $watermarkedContent = file_get_contents($watermarkedPath);
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
        $this->saveImage($image, $outputPath, $mimeType);

        // Clean up image resource
        imagedestroy($image);

        return $outputPath;
    }

    /**
     * Create image resource from file.
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
    private function drawWatermark($image, string $watermarkText, int $width, int $height): void
    {
        // Calculate watermark position
        $position = $this->calculateWatermarkPosition($width, $height, $watermarkText);

        // Create watermark background
        $watermarkWidth = $position['width'];
        $watermarkHeight = $position['height'];
        $watermark = imagecreatetruecolor($watermarkWidth, $watermarkHeight);

        // Set background color with opacity
        $backgroundColor = $this->hexToRgb($this->config['background_color']);
        $bgColor = imagecolorallocatealpha(
            $watermark,
            $backgroundColor['r'],
            $backgroundColor['g'],
            $backgroundColor['b'],
            (1 - $this->config['opacity']) * 127
        );
        imagefill($watermark, 0, 0, $bgColor);

        // Set text color
        $textColor = $this->hexToRgb($this->config['font_color']);
        $textColorResource = imagecolorallocate(
            $watermark,
            $textColor['r'],
            $textColor['g'],
            $textColor['b']
        );

        // Draw text
        $fontSize = $this->config['font_size'];
        $textX = ($watermarkWidth - strlen($watermarkText) * $fontSize * 0.6) / 2;
        $textY = ($watermarkHeight + $fontSize) / 2;

        imagestring($watermark, 5, $textX, $textY, $watermarkText, $textColorResource);

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
            $this->config['opacity'] * 100
        );

        // Clean up watermark resource
        imagedestroy($watermark);
    }

    /**
     * Calculate watermark position.
     */
    private function calculateWatermarkPosition(int $width, int $height, string $watermarkText): array
    {
        $fontSize = $this->config['font_size'];
        $margin = $this->config['margin'];

        // Calculate watermark dimensions
        $watermarkWidth = strlen($watermarkText) * $fontSize * 0.6 + $margin * 2;
        $watermarkHeight = $fontSize + $margin * 2;

        // Calculate position based on config
        $position = $this->config['position'];

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
            'x' => max(0, $x),
            'y' => max(0, $y),
            'width' => $watermarkWidth,
            'height' => $watermarkHeight,
        ];
    }

    /**
     * Save image to file.
     */
    private function saveImage($image, string $outputPath, string $mimeType): void
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
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Update watermark configuration.
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get watermark configuration.
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
     * Set watermark text.
     */
    public function setText(string $text): void
    {
        $this->config['text'] = $text;
    }

    /**
     * Set watermark position.
     */
    public function setPosition(string $position): void
    {
        $validPositions = ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];

        if (! in_array($position, $validPositions)) {
            throw new Exception('Invalid watermark position: '.$position);
        }

        $this->config['position'] = $position;
    }

    /**
     * Set watermark opacity.
     */
    public function setOpacity(float $opacity): void
    {
        if ($opacity < 0 || $opacity > 1) {
            throw new Exception('Opacity must be between 0 and 1');
        }

        $this->config['opacity'] = $opacity;
    }

    /**
     * Set watermark font size.
     */
    public function setFontSize(int $fontSize): void
    {
        if ($fontSize < 8 || $fontSize > 72) {
            throw new Exception('Font size must be between 8 and 72');
        }

        $this->config['font_size'] = $fontSize;
    }

    /**
     * Set watermark colors.
     */
    public function setColors(string $textColor, string $backgroundColor): void
    {
        $this->config['font_color'] = $textColor;
        $this->config['background_color'] = $backgroundColor;
    }
}
