<?php

declare(strict_types=1);

namespace App\Schemas;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
 *     @OA\Property(property="slug", type="string", example="iphone-15-pro"),
 *     @OA\Property(property="description", type="string", example="Latest iPhone with advanced features"),
 *     @OA\Property(property="price", type="number", format="float", example=999.99),
 *     @OA\Property(property="image", type="string", nullable=true, example="https://example.com/image.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="brand_id", type="integer", example=1),
 *     @OA\Property(property="store_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="brand", ref="#/components/schemas/Brand"),
 *     @OA\Property(property="category", ref="#/components/schemas/Category"),
 *     @OA\Property(property="price_offers", type="array", @OA\Items(ref="#/components/schemas/PriceOffer"))
 * )
 */
final class ProductSchema {}

/**
 * @OA\Schema(
 *     schema="ProductDetail",
 *     type="object",
 *     title="Product Detail",
 *     description="Detailed product information",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Product"),
 *         @OA\Schema(
 *
 *             @OA\Property(property="reviews", type="array", @OA\Items(ref="#/components/schemas/Review")),
 *         )
 *     }
 * )
 */
final class ProductDetailSchema {}

/**
 * @OA\Schema(
 *     schema="ProductCreateRequest",
 *     type="object",
 *     title="Product Create Request",
 *     description="Request data for creating a product",
 *     required={"name", "slug", "price", "category_id", "brand_id"},
 *
 *     @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
 *     @OA\Property(property="slug", type="string", example="iphone-15-pro"),
 *     @OA\Property(property="description", type="string", example="Latest iPhone with advanced features"),
 *     @OA\Property(property="price", type="number", format="float", example=999.99),
 *     @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="brand_id", type="integer", example=1),
 *     @OA\Property(property="store_id", type="integer", example=1)
 * )
 */
final class ProductCreateRequestSchema {}

/**
 * @OA\Schema(
 *     schema="ProductUpdateRequest",
 *     type="object",
 *     title="Product Update Request",
 *     description="Request data for updating a product",
 *
 *     @OA\Property(property="name", type="string", example="iPhone 15 Pro Max"),
 *     @OA\Property(property="slug", type="string", example="iphone-15-pro-max"),
 *     @OA\Property(property="description", type="string", example="Updated description"),
 *     @OA\Property(property="price", type="number", format="float", example=1099.99),
 *     @OA\Property(property="image", type="string", example="https://example.com/new-image.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=false),
 *     @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="brand_id", type="integer", example=1),
 *     @OA\Property(property="store_id", type="integer", example=2)
 * )
 */
final class ProductUpdateRequestSchema {}

/**
 * @OA\Schema(
 *     schema="Brand",
 *     type="object",
 *     title="Brand",
 *     description="Brand model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Apple"),
 *     @OA\Property(property="slug", type="string", example="apple"),
 *     @OA\Property(property="logo", type="string", nullable=true, example="https://example.com/logo.png"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
final class BrandSchema {}

/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     description="Category model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Smartphones"),
 *     @OA\Property(property="slug", type="string", example="smartphones"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Mobile phones and accessories"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
final class CategorySchema {}

/**
 * @OA\Schema(
 *     schema="PriceOffer",
 *     type="object",
 *     title="Price Offer",
 *     description="Price offer model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(property="store_id", type="integer", example=1),
 *     @OA\Property(property="price", type="number", format="float", example=899.99),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="product_url", type="string", example="https://store.com/product"),
 *     @OA\Property(property="affiliate_url", type="string", nullable=true, example="https://affiliate.com/product"),
 *     @OA\Property(property="in_stock", type="boolean", example=true),
 *     @OA\Property(property="stock_quantity", type="integer", nullable=true, example=50),
 *     @OA\Property(property="condition", type="string", example="new"),
 *     @OA\Property(property="rating", type="number", format="float", nullable=true, example=4.5),
 *     @OA\Property(property="reviews_count", type="integer", example=120),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/image.jpg"),
 *     @OA\Property(property="specifications", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
final class PriceOfferSchema {}
