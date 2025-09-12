<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cart_items extends Model
{
    //
    protected $fillable = [
        'product_id',
        'cart_id',
        'quantity',
        'price'
    ];

    public function cart(){
        return $this->belongsTo(Cart::class);
    }

    public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}


}
