<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $jar_id
 * @property numeric $amount
 * @property string|null $category
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $category_name
 * @property-read mixed $formatted_amount
 * @property-read \App\Models\Jar|null $jar
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome betweenDates($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome byCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome byMonth($month, $year = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome fromJar($jarId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome latest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereJarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outcome whereUserId($value)
 * @mixin \Eloquent
 */
class Outcome extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jar_id',
        'amount',
        'category',
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
    
    // Outcome thuộc về 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Outcome thuộc về 1 jar (có thể null)
    public function jar()
    {
        return $this->belongsTo(Jar::class);
    }

    /**
     * Scopes
     */
    
    // Lấy chi tiêu theo tháng
    public function scopeByMonth($query, $month, $year = null)
    {
        $year = $year ?? now()->year;
        return $query->whereMonth('date', $month)
                    ->whereYear('date', $year);
    }
    
    // Lấy chi tiêu trong khoảng thời gian
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
    
    // Lấy chi tiêu theo danh mục
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    // Lấy chi tiêu từ jar cụ thể
    public function scopeFromJar($query, $jarId)
    {
        return $query->where('jar_id', $jarId);
    }
    
    // Sắp xếp theo ngày mới nhất
    public function scopeLatest($query)
    {
        return $query->orderBy('date', 'desc');
    }

    /**
     * Constants cho các danh mục chi tiêu
     */
    const CATEGORIES = [
        'food' => 'Ăn uống',
        'transport' => 'Di chuyển',
        'shopping' => 'Mua sắm',
        'entertainment' => 'Giải trí',
        'health' => 'Y tế',
        'education' => 'Giáo dục',
        'bills' => 'Hóa đơn',
        'charity' => 'Từ thiện',
        'investment' => 'Đầu tư',
        'other' => 'Khác'
    ];

    /**
     * Helper methods
     */
    
    // Format số tiền
    public function getFormattedAmountAttribute()
    {
        return number_format((float) $this->amount, 0, ',', '.') . ' VND';
    }
    
    // Lấy tên danh mục tiếng Việt
    public function getCategoryNameAttribute()
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
    
    // Lấy chi tiêu hôm nay
    public static function today()
    {
        return static::where('date', today());
    }
    
    // Lấy chi tiêu tháng này
    public static function thisMonth()
    {
        return static::byMonth(now()->month, now()->year);
    }
    
    // Lấy chi tiêu năm này
    public static function thisYear()
    {
        return static::whereYear('date', now()->year);
    }

    /**
     * Boot method để tự động trừ tiền từ jar khi tạo outcome
     */
    protected static function boot()
    {
        parent::boot();
        // Balance adjustments are handled in the OutcomeController inside DB transactions.
    }
}