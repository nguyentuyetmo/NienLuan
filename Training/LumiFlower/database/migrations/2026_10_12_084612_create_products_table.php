<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->string('category')->nullable();       // Tên danh mục hiển thị (VD: Giỏ hoa)
        $table->string('category_slug')->nullable();  // Slug để lọc URL (?category=gio-hoa)
        $table->string('sku')->nullable();            // Mã sản phẩm (VD: BH-09)
        $table->decimal('price', 12, 2)->default(0);
        $table->decimal('old_price', 12, 2)->nullable();
        $table->string('image')->nullable();          // Ảnh đại diện chính
        $table->json('images')->nullable();           // Danh sách ảnh phụ (thumbnails)
        $table->boolean('in_stock')->default(true);   // Trạng thái còn hàng
        $table->text('description')->nullable();
        $table->json('details')->nullable();          // Bảng thông số thành phần hoa
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};