<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jar extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'percentage',
        'balance'
    ];

    /**
     * Data casting
     */
    protected $casts = [
        'percentage' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    
    // Jar thuộc về 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // 1 Jar có nhiều outcomes (chi tiêu)
    public function outcomes()
    {
        return $this->hasMany(Outcome::class);
    }

    /**
     * Constants cho các loại jar
     */
    const JAR_TYPES = [
        'NEC' => 'Necessities', // Nhu cầu thiết yếu (55%)
        'FFA' => 'Financial Freedom Account', // Tài khoản tự do tài chính (10%)
        'EDU' => 'Education', // Giáo dục (10%)
        'LTSS' => 'Long Term Saving for Spending', // Tiết kiệm dài hạn (10%)
        'PLAY' => 'Play', // Giải trí (10%)
        'GIVE' => 'Give', // Từ thiện (5%)
    ];

    /**
     * Scopes
     */
    
    // Lấy jar theo loại
    public function scopeOfType($query, $type)
    {
        return $query->where('name', $type);
    }
    
    // Lấy jar có số dư > 0
    public function scopeHasBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    /**
     * Helper methods
     */
    
    // Lấy tên đầy đủ của jar
    public function getFullNameAttribute()
    {
        return self::JAR_TYPES[$this->name] ?? $this->name;
    }
    
    // Thêm tiền vào jar
    public function addMoney($amount)
    {
        $this->increment('balance', $amount);
        return $this;
    }
    
    // Trừ tiền khỏi jar
    public function subtractMoney($amount)
    {
        if ($this->balance >= $amount) {
            $this->decrement('balance', $amount);
            return true;
        }
        return false;
    }
}