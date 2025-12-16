<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReport extends Model
{
    use HasFactory;

    protected $table = 'sales_reports';

    protected $fillable = [
        'store_id',
        'product_id',
        'product_name',
        'series',
        'total_sold',
        'total_revenue',
    ];
}
