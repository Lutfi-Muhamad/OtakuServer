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
    public function store(){return $this->belongsTo(\App\Models\Store::class, 'store_id');}
    
    protected $fillable = [
        'name',
        'category',
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
