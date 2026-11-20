<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 3 vai trò: admin (Quản trị viên), seller (Người bán hoa), customer (Khách hàng)
            $table->string('role', 20)->default('customer')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        // Đồng bộ dữ liệu cũ từ cột is_admin sang cột role
        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};