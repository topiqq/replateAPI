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
            $table->string('seller');
            $table->integer('original_price');
            $table->integer('surplus_price');
            $table->integer('stock');
            $table->string('emoji')->default('🍱');
            $table->string('tag'); //e.g 'Roti & Pastri' atau 'Makanan Berat'
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
