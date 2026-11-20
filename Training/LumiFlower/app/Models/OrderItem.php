<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_name', 'price', 'quantity'];

    // Liên kết về đơn hàng tổng
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Liên kết tới mẫu hoa (để xác định user_id chủ shop của sản phẩm này)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}