<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property numeric $percentage
 * @property numeric $balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Outcome> $outcomes
 * @property-read int|null $outcomes_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar hasBalance()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar ofType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jar whereUserId($value)
 * @mixin \Eloquent
 */
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