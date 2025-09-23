<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PriceOffer;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialTransactionService
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Update product price with transaction safety.
     */
    public function updateProductPrice(Product $product, float $newPrice, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($product, $newPrice, $reason): true {
            try {
                $oldPrice = $product->price;

                // Validate price
                if ($newPrice < 0) {
                    throw new Exception('Price cannot be negative');
                }

                if ($newPrice > 1000000) {
                    throw new Exception('Price exceeds maximum allowed value');
                }

                // Update product price
                $product->update(['price' => $newPrice]);

                // Log the transaction
                $this->auditService->logUpdated($product, ['price' => $oldPrice], [
                    'reason' => $reason,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'price_change' => $newPrice - $oldPrice,
                    'percentage_change' => $oldPrice > 0 ? (($newPrice - $oldPrice) / $oldPrice) * 100 : 0,
                ]);

                // Check for price alerts
                $this->checkPriceAlerts();

                Log::info('Product price updated successfully', [
                    'product_id' => $product->id,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'reason' => $reason,
                ]);

                return true;
            } catch (Exception $e) {
                Log::error('Failed to update product price', [
                    'product_id' => $product->id,
                    'new_price' => $newPrice,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Create price offer with transaction safety.
     *
     * @param  array<string, mixed>  $offerData
     */
    /**
     * @param  array<string, mixed>  $offerData
     */
    public function createPriceOffer(array $offerData): PriceOffer
    {
        return DB::transaction(function () use ($offerData) {
            try {
                // Validate offer data
                $this->validateOfferData($offerData);

                // Create price offer
                $offer = PriceOffer::create($offerData);

                // Log the transaction
                $this->auditService->logCreated($offer, [
                    'product_id' => $offer->product_id,
                    'store_id' => $offer->store_id,
                    'price' => $offer->price,
                ]);

                // Update product price if this is the lowest offer
                $this->updateProductPriceIfNeeded($offer);

                Log::info('Price offer created successfully', [
                    'offer_id' => $offer->id,
                    'product_id' => $offer->product_id,
                    'store_id' => $offer->store_id,
                    'price' => $offer->price,
                ]);

                return $offer;
            } catch (Exception $e) {
                Log::error('Failed to create price offer', [
                    'offer_data' => $offerData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Update price offer with transaction safety.
     *
     * @param  array<string, mixed>  $updateData
     */
    public function updatePriceOffer(PriceOffer $offer, array $updateData): bool
    {
        return DB::transaction(function () use ($offer, $updateData): true {
            try {
                $oldData = $offer->toArray();

                // Validate update data
                if (isset($updateData['price'])) {
                    $price = is_numeric($updateData['price']) ? (float) $updateData['price'] : 0.0;
                    $this->validatePrice($price);
                }

                // Update offer
                $offer->update($updateData);

                // Log the transaction
                $this->auditService->logUpdated($offer, $oldData, [
                    'changes' => $offer->getChanges(),
                ]);

                // Update product price if needed
                $this->updateProductPriceIfNeeded($offer);

                Log::info('Price offer updated successfully', [
                    'offer_id' => $offer->id,
                    'changes' => $offer->getChanges(),
                ]);

                return true;
            } catch (Exception $e) {
                Log::error('Failed to update price offer', [
                    'offer_id' => $offer->id,
                    'update_data' => $updateData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Delete price offer with transaction safety.
     */
    public function deletePriceOffer(PriceOffer $offer): bool
    {
        return DB::transaction(function () use ($offer): true {
            try {
                $product = $offer->product;
                $wasLowestPrice = $this->isLowestPriceOffer($offer);

                // Delete offer
                $offer->delete();

                // Log the transaction
                $this->auditService->logDeleted($offer, [
                    'product_id' => $product->id,
                    'was_lowest_price' => $wasLowestPrice,
                ]);

                // Update product price if this was the lowest offer
                if ($wasLowestPrice && $product) {
                    $this->updateProductPriceFromOffers($product);
                }

                Log::info('Price offer deleted successfully', [
                    'offer_id' => $offer->id,
                    'product_id' => $product->id,
                ]);

                return true;
            } catch (Exception $e) {
                Log::error('Failed to delete price offer', [
                    'offer_id' => $offer->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Validate offer data.
     *
     * @param  array<string, mixed>  $data
     */
    private function validateOfferData(array $data): void
    {
        if (! isset($data['product_id']) || ! isset($data['store_id']) || ! isset($data['price'])) {
            throw new Exception('Missing required fields: product_id, store_id, price');
        }

        $price = is_numeric($data['price']) ? (float) $data['price'] : 0.0;
        $this->validatePrice($price);

        // Check if product exists
        if (! Product::find($data['product_id'])) {
            throw new Exception('Product not found');
        }
    }

    /**
     * Validate price.
     */
    private function validatePrice(float $price): void
    {
        if ($price < 0) {
            throw new Exception('Price cannot be negative');
        }

        if ($price > 1000000) {
            throw new Exception('Price exceeds maximum allowed value');
        }
    }

    /**
     * Check if offer is the lowest price.
     */
    private function isLowestPriceOffer(PriceOffer $offer): bool
    {
        $lowestOffer = PriceOffer::where('product_id', $offer->product_id)
            ->where('is_available', true)
            ->orderBy('price')
            ->first();

        return $lowestOffer && $lowestOffer->id === $offer->id;
    }

    /**
     * Update product price if this offer is the lowest.
     */
    private function updateProductPriceIfNeeded(PriceOffer $offer): void
    {
        $lowestOffer = PriceOffer::where('product_id', $offer->product_id)
            ->where('is_available', true)
            ->orderBy('price')
            ->first();

        if ($lowestOffer && $offer->product instanceof \App\Models\Product && $lowestOffer->price !== $offer->product->price) {
            $this->updateProductPrice($offer->product, (float) $lowestOffer->price, 'Updated from lowest price offer');
        }
    }

    /**
     * Update product price from all offers.
     */
    private function updateProductPriceFromOffers(Product $product): void
    {
        $lowestOffer = PriceOffer::where('product_id', $product->id)
            ->where('is_available', true)
            ->orderBy('price')
            ->first();

        if ($lowestOffer) {
            $this->updateProductPrice($product, (float) $lowestOffer->price, 'Updated from remaining offers');
        }
    }

    /**
     * Check for price alerts.
     */
    private function checkPriceAlerts(): void
    {
        // This would integrate with the notification system
        // to send alerts when price drops below target
    }
}
