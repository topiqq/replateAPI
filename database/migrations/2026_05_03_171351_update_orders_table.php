<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Siapa yang beli — wajib untuk riwayat pesanan per UMKM
            $table->foreignId('buyer_id')->nullable()->after('product_id')
                  ->constrained('users')->nullOnDelete()
                  ->comment('FK ke users (role: umkm)');

            // Berat total — dihitung dari products.weight_kg x quantity
            $table->decimal('total_weight_kg', 8, 2)->default(0)->after('total_price')
                  ->comment('Otomatis: weight_kg x quantity');

            // Status pesanan yang lebih lengkap
            // Ganti kolom status lama (string) dengan enum yang proper
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'processing', 'ready', 'completed', 'cancelled'])
                  ->default('pending')->after('total_weight_kg');

            // Payment gateway
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])
                  ->default('unpaid')->after('status');
            $table->string('snap_token')->nullable()->after('payment_status')
                  ->comment('Token Midtrans Snap untuk redirect pembayaran');
            $table->string('payment_url')->nullable()->after('snap_token')
                  ->comment('URL redirect ke halaman bayar Midtrans');

            // Hapus customer_name karena sudah ada buyer_id
            if (Schema::hasColumn('orders', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
            $table->dropColumn(['buyer_id', 'total_weight_kg', 'status', 'payment_status', 'snap_token', 'payment_url']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('Diproses');
            $table->string('customer_name')->default('UMKM');
        });
    }
};
