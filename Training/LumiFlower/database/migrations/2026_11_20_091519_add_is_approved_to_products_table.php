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
        $table->boolean('is_approved')->default(false)->after('stock');
    });

    // Tự động duyệt toàn bộ các mẫu hoa có sẵn trước đó
    \Illuminate\Support\Facades\DB::table('products')->update(['is_approved' => true]);
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('is_approved');
    });
}
};
