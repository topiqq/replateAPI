<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Verifikasi identitas
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                  ->default('pending')
                  ->after('shop_name');
            $table->string('verification_file')->nullable()->after('verification_status');

            // Lokasi (kalau belum ada)
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('verification_file');
            }
            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verification_status', 'verification_file']);
            if (Schema::hasColumn('users', 'latitude'))  $table->dropColumn('latitude');
            if (Schema::hasColumn('users', 'longitude')) $table->dropColumn('longitude');
        });
    }
};
