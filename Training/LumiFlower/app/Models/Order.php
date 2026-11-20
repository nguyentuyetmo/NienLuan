<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_code', 'user_id', 'customer_name', 'customer_phone', 'customer_email',
        'province', 'district', 'ward', 'address', 'delivery_date', 'delivery_time',
        'card_message', 'payment_method', 'subtotal', 'shipping_fee', 'discount', 'total', 'status'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}