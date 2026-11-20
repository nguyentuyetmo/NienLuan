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
        'category',
        'category_slug',
        'sku',
        'price',
        'old_price',
        'image',
        'images',
        'in_stock',
        'description',
        'details',
        'is_approved',
        'user_id',
    ];

    protected $casts = [
        'images' => 'array',
        'details' => 'array',
        'in_stock' => 'boolean',
    ];
    public function seller()
{
    return $this->belongsTo(User::class, 'user_id');
}
}