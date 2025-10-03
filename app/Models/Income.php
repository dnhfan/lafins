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
    
    // Lấy thu nhập theo tháng
    public function scopeByMonth($query, $month, $year = null)
    {
        $year = $year ?? now()->year;
        return $query->whereMonth('date', $month)
                    ->whereYear('date', $year);
    }
    
    // Lấy thu nhập trong khoảng thời gian
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
    
    // Lấy thu nhập theo nguồn
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }
    
    // Sắp xếp theo ngày mới nhất
    public function scopeLatest($query)
    {
        return $query->orderBy('date', 'desc');
    }

    /**
     * Helper methods
     */
    
    // Format số tiền
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', '.') . ' VND';
    }
    
    // Lấy thu nhập hôm nay
    public static function today()
    {
        return static::whereDate('date', today());
    }
    
    // Lấy thu nhập tháng này
    public static function thisMonth()
    {
        return static::byMonth(now()->month, now()->year);
    }
    
    // Lấy thu nhập năm này
    public static function thisYear()
    {
        return static::whereYear('date', now()->year);
    }
}