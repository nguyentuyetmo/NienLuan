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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('order_code')->unique(); // Mã đơn: LUMI-XXXXXX
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('customer_name');
        $table->string('customer_phone');
        $table->string('customer_email')->nullable();
        $table->string('province');
        $table->string('district');
        $table->string('ward');
        $table->string('address');
        $table->date('delivery_date');
        $table->string('delivery_time');
        $table->text('card_message')->nullable();
        $table->string('payment_method')->default('cod'); // cod | bank
        $table->integer('subtotal');
        $table->integer('shipping_fee')->default(30000);
        $table->integer('discount')->default(0);
        $table->integer('total');
        $table->string('status')->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
