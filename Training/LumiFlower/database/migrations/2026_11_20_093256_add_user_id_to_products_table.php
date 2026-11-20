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
    Schema::table('products', function (Blueprint $table) {
        $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
    });

    // Gán tạm các sản phẩm cũ cho tài khoản Admin hoặc Seller đầu tiên
    $defaultOwnerId = \Illuminate\Support\Facades\DB::table('users')
        ->whereIn('role', ['seller', 'admin'])
        ->value('id');

    if ($defaultOwnerId) {
        \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('user_id')
            ->update(['user_id' => $defaultOwnerId]);
    }
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropConstrainedForeignId('user_id');
    });
}
};
