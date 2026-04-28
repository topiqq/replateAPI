<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'seller',
        'original_price',
        'surplus_price',
        'stock',
        'emoji',
        'tag',
    ];

    // Relasi ke user (Partner pemilik produk)
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
