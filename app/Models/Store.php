<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['user_id', 'name', 'phone', 'address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
