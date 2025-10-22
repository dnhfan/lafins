<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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