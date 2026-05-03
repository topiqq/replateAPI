<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'product_id',
        'buyer_id',          // FK ke users (role: umkm)
        'quantity',
        'total_price',
        'total_weight_kg',   // dihitung otomatis: weight_kg x quantity
        'status',            // pending | paid | processing | ready | completed | cancelled
        'payment_status',    // unpaid | paid | refunded
        'snap_token',        // token Midtrans
        'payment_url',       // URL redirect Midtrans
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'total_price'     => 'integer',
        'total_weight_kg' => 'decimal:2',
    ];

    // ── Relasi ───────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // ── Boot — otomatis hitung total_weight_kg & total_price ──

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $product = Product::find($order->product_id);

            if ($product) {
                // Hitung total harga
                $order->total_price = $product->surplus_price * $order->quantity;

                // Hitung total berat untuk impact dashboard
                $order->total_weight_kg = $product->weight_kg * $order->quantity;

                // Kurangi stok produk
                $product->decrement('stock', $order->quantity);
            }
        });

        // Kembalikan stok jika pesanan dibatalkan
        static::updating(function (Order $order) {
            if ($order->isDirty('status') && $order->status === 'cancelled') {
                $order->product?->increment('stock', $order->quantity);
                $order->payment_status = 'refunded';
            }
        });
    }

    // ── Helper status ────────────────────────────────────

    public function isPaid(): bool      { return $this->payment_status === 'paid'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
}
