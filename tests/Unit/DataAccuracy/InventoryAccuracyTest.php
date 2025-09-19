<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InventoryAccuracyTest extends TestCase
{
    #[Test]
    public function it_validates_inventory_quantities(): void
    {
        $inventory = [
            'product_1' => 100,
            'product_2' => 50,
            'product_3' => 0,
            'product_4' => -5, // Invalid negative quantity
        ];

        $validInventory = $this->validateInventoryQuantities($inventory);

        $this->assertArrayNotHasKey('product_4', $validInventory);
        $this->assertEquals(100, $validInventory['product_1']);
        $this->assertEquals(50, $validInventory['product_2']);
        $this->assertEquals(0, $validInventory['product_3']);
    }

    #[Test]
    public function it_tracks_inventory_changes(): void
    {
        $initialQuantity = 100;
        $soldQuantity = 25;
        $expectedFinalQuantity = 75;

        $finalQuantity = $this->updateInventory($initialQuantity, -$soldQuantity);

        $this->assertEquals($expectedFinalQuantity, $finalQuantity);
    }

    #[Test]
    public function it_handles_inventory_restocking(): void
    {
        $currentQuantity = 25;
        $restockQuantity = 50;
        $expectedFinalQuantity = 75;

        $finalQuantity = $this->updateInventory($currentQuantity, $restockQuantity);

        $this->assertEquals($expectedFinalQuantity, $finalQuantity);
    }

    #[Test]
    public function it_detects_low_stock_levels(): void
    {
        $inventory = [
            'product_1' => 5,   // Low stock
            'product_2' => 50,  // Normal stock
            'product_3' => 0,    // Out of stock
        ];

        $lowStockProducts = $this->getLowStockProducts($inventory, 10);

        $this->assertContains('product_1', $lowStockProducts);
        $this->assertContains('product_3', $lowStockProducts);
        $this->assertNotContains('product_2', $lowStockProducts);
    }

    #[Test]
    public function it_calculates_inventory_turnover(): void
    {
        $averageInventory = 1000;
        $costOfGoodsSold = 5000;
        $expectedTurnover = 5.0;

        $turnover = $this->calculateInventoryTurnover($averageInventory, $costOfGoodsSold);

        $this->assertEquals($expectedTurnover, $turnover);
    }

    #[Test]
    public function it_validates_inventory_accuracy(): void
    {
        $systemQuantity = 100;
        $physicalQuantity = 95;
        $tolerance = 0.05; // 5%

        $isAccurate = $this->isInventoryAccurate($systemQuantity, $physicalQuantity, $tolerance);

        $this->assertTrue($isAccurate);
    }

    private function validateInventoryQuantities(array $inventory): array
    {
        return array_filter($inventory, function ($quantity) {
            return $quantity >= 0;
        });
    }

    private function updateInventory(int $currentQuantity, int $change): int
    {
        return max(0, $currentQuantity + $change);
    }

    private function getLowStockProducts(array $inventory, int $threshold): array
    {
        return array_keys(array_filter($inventory, function ($quantity) use ($threshold) {
            return $quantity <= $threshold;
        }));
    }

    private function calculateInventoryTurnover(float $averageInventory, float $costOfGoodsSold): float
    {
        return $costOfGoodsSold / $averageInventory;
    }

    private function isInventoryAccurate(int $systemQuantity, int $physicalQuantity, float $tolerance): bool
    {
        $difference = abs($systemQuantity - $physicalQuantity);
        $maxDifference = $systemQuantity * $tolerance;

        return $difference <= $maxDifference;
    }
}
