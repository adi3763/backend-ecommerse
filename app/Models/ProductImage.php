<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    //
    protected $fillable = [
        'image',
        'product_id',
        'is_primary', // optional helper
    ];

       protected $appends  = ['image_url'];   // <-- expose computed URL
    protected $hidden   = ['image'];       // optional: hide raw path

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        // Converts 'products/abc.jpg' -> '/storage/products/abc.jpg'
        // and wraps with APP_URL via asset()
        return asset(Storage::url($this->image));
    }
}
