<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',          // FK ke users (partner pemilik)
        'name',
        'seller',           // nama toko (denormalized, untuk tampilan cepat)
        'original_price',
        'surplus_price',
        'stock',
        'weight_kg',        // berat per item — untuk impact dashboard
        'image',            // path foto produk (nullable)
        'emoji',
        'tag',
    ];

    protected $casts = [
        'original_price' => 'integer',
        'surplus_price'  => 'integer',
        'stock'          => 'integer',
        'weight_kg'      => 'decimal:2',
    ];

    // ── Relasi ───────────────────────────────────────────

    // Partner pemilik produk ini
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Semua pesanan untuk produk ini
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ── Accessor ─────────────────────────────────────────

    // Selisih harga asli vs surplus
    public function getDiscountAmountAttribute(): int
    {
        return $this->original_price - $this->surplus_price;
    }

    // Persentase diskon
    public function getDiscountPercentAttribute(): int
    {
        if ($this->original_price === 0) return 0;
        return (int) round((($this->original_price - $this->surplus_price) / $this->original_price) * 100);
    }
}
