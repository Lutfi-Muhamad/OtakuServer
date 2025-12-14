<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // relasi ke cart
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'folder',
        'image_key',
        'image_type',
        'aspect_ratio',
        'store_id'
    ];
}
