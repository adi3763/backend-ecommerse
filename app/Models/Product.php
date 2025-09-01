<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable =[
        'id',
        'title',
        'price',
        'comparable_price',
        'description',
        'short-desc',
        'category_id',
        'brand_id',
        'qty',
        'sku',
        'barcode',
        'status',
        'is_featured'
    ];

     protected $appends  = ['primary_image_url'];

    public function images()
{
    return $this->hasMany(ProductImage::class)->orderBy('id');
}

public function primaryImage()
{
    return $this->hasOne(ProductImage::class)->where('is_primary', true);
}


    public function getPrimaryImageUrlAttribute()
    {
        // prefer primary; else first image
        return optional($this->primaryImage)->image_url
            ?? optional($this->images()->orderBy('id')->first())->image_url;
    }
}
