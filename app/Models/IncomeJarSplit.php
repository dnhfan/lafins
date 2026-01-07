<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $income_id
 * @property int $jar_id
 * @property numeric $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Income $income
 * @property-read \App\Models\Jar $jar
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit whereIncomeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit whereJarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeJarSplit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IncomeJarSplit extends Model
{
    use HasFactory;

    protected $table = 'income_jar_splits';

    protected $fillable = [
        'income_id',
        'jar_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function income()
    {
        return $this->belongsTo(Income::class);
    }

    public function jar()
    {
        return $this->belongsTo(Jar::class);
    }
}
