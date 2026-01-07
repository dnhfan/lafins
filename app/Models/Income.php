<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property numeric $amount
 * @property string|null $source
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income betweenDates($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereUserId($value)
 * @mixin \Eloquent
 */
class Income extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount', 
        'source',
        'description',
        'date'
    ];

    /**
     * Data casting
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Relationships
     */
    
    // Income thuộc về 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    
    // Lấy thu nhập trong khoảng thời gian
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}