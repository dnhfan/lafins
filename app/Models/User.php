<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    

    /**
     * The attributes that should be hidden for serialization.
     * Added common two-factor fields so they won't be exposed by APIs.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

     /**
     * Relationships
     */
    
    // 1. User có nhiều thu nhập (incomes)
    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    // 2. User có nhiều chi tiêu (outcomes)  
    public function outcomes()
    {
        return $this->hasMany(Outcome::class);
    }

    // 3. User có nhiều hũ tiền (jars)
    public function jars()
    {
        return $this->hasMany(Jar::class);
    }

    /**
     * Helper methods
     */
    
    // Lấy tổng thu nhập
    public function getTotalIncomeAttribute()
    {
        return $this->incomes()->sum('amount');
    }
    
    // Lấy tổng chi tiêu
    public function getTotalOutcomeAttribute()
    {
        return $this->outcomes()->sum('amount');
    }
    
    // Lấy số dư tổng trong tất cả các hũ
    public function getTotalJarBalanceAttribute()
    {
        return $this->jars()->sum('balance');
    }
}
