<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
