<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'brand_id',
        'image_url',
        'model_number',
        'specifications',
        'is_active',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    
    public function category() 
    {
        return $this->belongsTo(Category::class);
    }
    
    public function priceOffers()
    {
        return $this->hasMany(PriceOffer::class);
    }
    
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    // ✅✅ العلاقة المفقودة التي تم إضافتها ✅✅
    public function priceAlerts()
    {
        return $this->hasMany(PriceAlert::class);
    }
}
