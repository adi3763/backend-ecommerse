<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items(){
        return $this->hasMany(cart_items::class);
    }
    public function products(){
        return $this->hasManyThrough(Product::class, cart_items::class, 'cart_id', 'id', 'id', 'product_id');
    }
}
