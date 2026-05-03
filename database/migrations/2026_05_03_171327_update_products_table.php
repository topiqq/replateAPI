<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Berat per item — wajib untuk impact dashboard
            $table->decimal('weight_kg', 8, 2)->default(0)->after('stock')
                  ->comment('Berat per item dalam kg, untuk hitung dampak lingkungan');

            // Foto produk (opsional tapi berguna untuk katalog)
            $table->string('image')->nullable()->after('weight_kg');

            // Pastikan user_id ada sebagai FK (skip kalau sudah ada)
            if (!Schema::hasColumn('products', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight_kg', 'image']);
        });
    }
};
