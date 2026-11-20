<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'product_id'])]
class Wishlist extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}