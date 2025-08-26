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
        'price',
        'is_active',
        'category_id',
        'brand_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // --- العلاقات ---
    public function brand() 
    { 
        return $this->belongsTo(Brand::class); 
    }
    
    public function category() 
    { 
        return $this->belongsTo(Category::class); 
    }
    
    public function priceAlerts() 
    { 
        return $this->hasMany(PriceAlert::class); 
    }
    
    public function reviews() 
    { 
        return $this->hasMany(Review::class); 
    }
    
    public function wishlists() 
    { 
        return $this->hasMany(Wishlist::class); 
    }
    
    public function priceOffers() 
    { 
        return $this->hasMany(PriceOffer::class); 
    }
}
