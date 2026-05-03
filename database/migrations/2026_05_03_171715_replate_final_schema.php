<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. USERS ─────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            // Ganti default role dari 'umkm' ke 'buyer'
            $table->string('role')->default('buyer')->change();

            // Verifikasi identitas
            if (!Schema::hasColumn('users', 'verification_status')) {
                $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                      ->default('pending')->after('shop_name');
            }
            if (!Schema::hasColumn('users', 'verification_file')) {
                $table->string('verification_file')->nullable()->after('verification_status');
            }

            // Lokasi
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('verification_file');
            }
            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        // Update data lama: role 'umkm' → 'buyer'
        DB::table('users')->where('role', 'umkm')->update(['role' => 'buyer']);

        // ── 2. PRODUCTS ──────────────────────────────────────────
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'weight_kg')) {
                $table->decimal('weight_kg', 8, 2)->default(0)->after('stock')
                      ->comment('Berat per item dalam kg');
            }
            if (!Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('weight_kg');
            }
        });

        // ── 3. ORDERS ────────────────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            // Rename user_id → buyer_id kalau masih pakai user_id
            if (Schema::hasColumn('orders', 'user_id') && !Schema::hasColumn('orders', 'buyer_id')) {
                $table->renameColumn('user_id', 'buyer_id');
            }

            if (!Schema::hasColumn('orders', 'total_weight_kg')) {
                $table->decimal('total_weight_kg', 8, 2)->default(0)->after('total_price');
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])
                      ->default('unpaid')->after('status');
            }
            if (!Schema::hasColumn('orders', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'payment_url')) {
                $table->string('payment_url')->nullable()->after('snap_token');
            }
        });
    }

    public function down(): void
    {
        // Rollback users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verification_status', 'verification_file', 'latitude', 'longitude']);
            $table->string('role')->default('umkm')->change();
        });
        DB::table('users')->where('role', 'buyer')->update(['role' => 'umkm']);

        // Rollback products
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight_kg', 'image']);
        });

        // Rollback orders
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'buyer_id')) {
                $table->renameColumn('buyer_id', 'user_id');
            }
            $table->dropColumn(['total_weight_kg', 'payment_status', 'snap_token', 'payment_url']);
        });
    }
};
