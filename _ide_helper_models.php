<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @template TFactory of BrandFactory
 * @mixin TFactory
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $logo_url
 * @property string|null $website_url
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Database\Factories\BrandFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereLogoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereWebsiteUrl($value)
 */
	class Brand extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 * @mixin TFactory
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $parent_id
 * @property int $level
 * @property string|null $description
 * @property string|null $image_url
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of CurrencyFactory
 * @mixin TFactory
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property string $exchange_rate
 * @property int $is_active
 * @property int $is_default
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Language> $languages
 * @property-read int|null $languages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Store> $stores
 * @property-read int|null $stores_count
 * @method static \Database\Factories\CurrencyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereUpdatedAt($value)
 */
	class Currency extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of LanguageFactory
 * @mixin TFactory
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $native_name
 * @property string $direction
 * @property bool $is_active
 * @property int $is_default
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Currency> $currencies
 * @property-read int|null $currencies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserLocaleSetting> $userLocaleSettings
 * @property-read int|null $user_locale_settings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language active()
 * @method static \Database\Factories\LanguageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereNativeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereUpdatedAt($value)
 */
	class Language extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of PriceAlertFactory
 * @mixin TFactory
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $target_price
 * @property string|null $current_price
 * @property string $currency
 * @property int $is_active
 * @property int $repeat_alert
 * @property string|null $last_checked_at
 * @property string|null $last_triggered_at
 * @property int $trigger_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PriceAlertFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereCurrentPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereLastCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereLastTriggeredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereRepeatAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereTargetPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereTriggerCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceAlert whereUserId($value)
 */
	class PriceAlert extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of PriceOfferFactory
 * @mixin TFactory
 * @property int $id
 * @property int $product_id
 * @property string|null $product_sku
 * @property int $store_id
 * @property numeric $price
 * @property string $currency
 * @property string|null $product_url
 * @property string|null $affiliate_url
 * @property bool $in_stock
 * @property int|null $stock_quantity
 * @property string $condition
 * @property numeric|null $rating
 * @property int $reviews_count
 * @property string|null $image_url
 * @property array<array-key, mixed>|null $specifications
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Store $store
 * @method static \Database\Factories\PriceOfferFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereAffiliateUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereInStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereProductSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereProductUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereReviewsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereStockQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceOffer whereUpdatedAt($value)
 */
	class PriceOffer extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of ProductFactory
 * @mixin TFactory
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property numeric $price
 * @property string|null $compare_at_price
 * @property bool $is_active
 * @property int|null $brand_id
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $image
 * @property-read \App\Models\Brand|null $brand
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceAlert> $priceAlerts
 * @property-read int|null $price_alerts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceOffer> $priceOffers
 * @property-read int|null $price_offers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wishlist> $wishlists
 * @property-read int|null $wishlists_count
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCompareAtPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of ReviewFactory
 * @mixin TFactory
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $title
 * @property string $content
 * @property int $rating
 * @property bool $is_verified_purchase
 * @property bool $is_approved
 * @property array<array-key, mixed>|null $helpful_votes
 * @property int $helpful_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $review_text
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\ReviewFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereHelpfulCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereHelpfulVotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereIsVerifiedPurchase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUserId($value)
 */
	class Review extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of StoreFactory
 * @mixin TFactory
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $logo
 * @property string|null $website_url
 * @property string $country_code
 * @property array<array-key, mixed>|null $supported_countries
 * @property int $is_active
 * @property int $priority
 * @property string|null $affiliate_base_url
 * @property array<array-key, mixed>|null $api_config
 * @property int $currency_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Currency $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceOffer> $priceOffers
 * @property-read int|null $price_offers_count
 * @method static \Database\Factories\StoreFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereAffiliateBaseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereApiConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereSupportedCountries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereWebsiteUrl($value)
 */
	class Store extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of UserFactory
 * @mixin TFactory
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_admin
 * @property-read \App\Models\UserLocaleSetting|null $localeSetting
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceAlert> $priceAlerts
 * @property-read int|null $price_alerts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wishlist> $wishlists
 * @property-read int|null $wishlists_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of UserLocaleSettingFactory
 * @mixin TFactory
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property int $language_id
 * @property int $currency_id
 * @property string|null $ip_address
 * @property string|null $country_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Currency $currency
 * @property-read \App\Models\Language $language
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\UserLocaleSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLocaleSetting whereUserId($value)
 */
	class UserLocaleSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * @template TFactory of WishlistFactory
 * @mixin TFactory
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\WishlistFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereUserId($value)
 */
	class Wishlist extends \Eloquent {}
}

