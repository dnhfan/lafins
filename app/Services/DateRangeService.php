<?php

namespace App\Services;

use Carbon\Carbon;

class DateRangeService
{
    public static function parse(?string $range = null, ?string $start = null, ?string $end = null)
    {
        $now = Carbon::now(config('app.timezone'));

        if ($range) {
            match ($range) {
                'day' => [$start, $end] = [$now->toDateString(), $now->toDateString()],
                'month' => [$start, $end] = [$now->firstOfMonth()->toDateString(), $now->endOfMonth()->toDateString()],
                'year' => [$start, $end] = [$now->firstOfYear()->toDateString(), $now->endOfYear()->toDateString()],
                default => [$start, $end] = [$now->toDateString(), $now->toDateString()],
            };
        }

        return [
            'start' => $start ?? $now->toDateString(),
            'end' => $end ?? $now->toDateString(),
        ];
    }
}
