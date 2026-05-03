<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',                  // admin | partner | buyer
        'shop_name',
        'verification_status',   // pending | verified | rejected
        'verification_file',
        'latitude',
        'longitude',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'latitude'          => 'decimal:7',
        'longitude'         => 'decimal:7',
    ];

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isPartner(): bool  { return $this->role === 'partner'; }
    public function isBuyer(): bool    { return $this->role === 'buyer'; }
    public function isVerified(): bool { return $this->verification_status === 'verified'; }

    public function products()
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function totalWeightSaved(): float
    {
        return $this->orders()->whereIn('status', ['completed', 'paid'])->sum('total_weight_kg');
    }

    public function totalSavings(): int
    {
        return $this->orders()->whereIn('status', ['completed', 'paid'])->with('product')->get()
            ->sum(fn($o) => ($o->product->original_price - $o->product->surplus_price) * $o->quantity);
    }
}
