<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_wishlist_item()
    {
        $wishlist = Wishlist::factory()->create([
            'user_id' => User::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'notes' => 'Test notes',
        ]);

        $this->assertInstanceOf(Wishlist::class, $wishlist);
        $this->assertEquals('Test notes', $wishlist->notes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_user_relationship()
    {
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $wishlist->user);
        $this->assertEquals($user->id, $wishlist->user->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_product_relationship()
    {
        $product = Product::factory()->create();
        $wishlist = Wishlist::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $wishlist->product);
        $this->assertEquals($product->id, $wishlist->product->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_required_fields()
    {
        $wishlist = new Wishlist;

        $this->assertFalse($wishlist->validate());
        $this->assertArrayHasKey('user_id', $wishlist->getErrors());
        $this->assertArrayHasKey('product_id', $wishlist->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_notes_length()
    {
        $wishlist = Wishlist::factory()->make(['notes' => str_repeat('a', 1001)]);

        $this->assertFalse($wishlist->validate());
        $this->assertArrayHasKey('notes', $wishlist->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_wishlist_for_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Wishlist::factory()->create(['user_id' => $user1->id]);
        Wishlist::factory()->create(['user_id' => $user2->id]);

        $user1Wishlist = Wishlist::forUser($user1->id)->get();

        $this->assertCount(1, $user1Wishlist);
        $this->assertEquals($user1->id, $user1Wishlist->first()->user_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_wishlist_for_product()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Wishlist::factory()->create(['product_id' => $product1->id]);
        Wishlist::factory()->create(['product_id' => $product2->id]);

        $product1Wishlist = Wishlist::forProduct($product1->id)->get();

        $this->assertCount(1, $product1Wishlist);
        $this->assertEquals($product1->id, $product1Wishlist->first()->product_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_soft_delete_wishlist_item()
    {
        $wishlist = Wishlist::factory()->create();

        $wishlist->delete();

        $this->assertSoftDeleted('wishlists', ['id' => $wishlist->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_restore_soft_deleted_wishlist_item()
    {
        $wishlist = Wishlist::factory()->create();
        $wishlist->delete();

        $wishlist->restore();

        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
            'deleted_at' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_product_is_in_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Wishlist::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertTrue(Wishlist::isProductInWishlist($user->id, $product->id));

        $anotherProduct = Product::factory()->create();
        $this->assertFalse(Wishlist::isProductInWishlist($user->id, $anotherProduct->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_add_product_to_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlist = Wishlist::addToWishlist($user->id, $product->id, 'Test notes');

        $this->assertInstanceOf(Wishlist::class, $wishlist);
        $this->assertEquals($user->id, $wishlist->user_id);
        $this->assertEquals($product->id, $wishlist->product_id);
        $this->assertEquals('Test notes', $wishlist->notes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_remove_product_from_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Wishlist::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $removed = Wishlist::removeFromWishlist($user->id, $product->id);

        $this->assertTrue($removed);
        $this->assertFalse(Wishlist::isProductInWishlist($user->id, $product->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_wishlist_count_for_user()
    {
        $user = User::factory()->create();

        Wishlist::factory()->count(3)->create(['user_id' => $user->id]);

        $count = Wishlist::getWishlistCount($user->id);

        $this->assertEquals(3, $count);
    }
}
