<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\PreservesFilters as PreservesFiltersTrait;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Income;
use App\Models\Jar;
use App\Models\Outcome;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;

class DashboardController extends Controller
{
    use PreservesFiltersTrait;
    use ApiResponse;

    /**
     * Show dashboard summary (balance, income, outcome) for a date range.
     * Accepts optional `start` and `end` query params (YYYY-MM-DD).
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // default timezone (could be user-specific)
            $tz = config('app.timezone');

            // Accept either a canonical `range` (day|month|year) or explicit start/end.
            $range = $request->query('range');
            $start = $request->query('start');
            $end = $request->query('end');

            $now = Carbon::now($tz);

            if ($range) {
                if ($range === 'day') {
                    $start = $now->toDateString();
                    $end = $now->toDateString();
                } elseif ($range === 'month') {
                    $start = $now->copy()->firstOfMonth()->toDateString();
                    $end = $now->copy()->endOfMonth()->toDateString();
                } elseif ($range === 'year') {
                    $start = $now->copy()->firstOfYear()->toDateString();
                    $end = $now->copy()->endOfYear()->toDateString();
                } else {
                    // unknown range -> fallback to today
                    $start = $now->toDateString();
                    $end = $now->toDateString();
                }
            }

            // fallback to today if start or end missing
            $start = $start ?? $now->toDateString();
            $end = $end ?? $now->toDateString();

            /** For Total money */

            // Totals for the range
            // Use the Income model's scopeBetweenDates() helper
            $totalIncome = (float) Income::where('user_id', $user->id)
                ->betweenDates($start, $end)
                ->sum('amount');

            $totalOutcome = (float) Outcome::where('user_id', $user->id)
                ->betweenDates($start, $end)
                ->sum('amount');

            // Total balance is sum of jars' balances for the user
            $totalBalance = (float) Jar::where('user_id', $user->id)->sum('balance');

            /** For Pie chart and jar list */

            // Load jars for this user and prepare configuration (percentage, balance, allocated amount)
            $jars = Jar::where('user_id', $user->id)
                ->orderBy('id')
                ->get(['id', 'name', 'percentage', 'balance']);

            // Map to a simple array for Inertia props
            $jarConfigs = $jars->map(function ($jar) use ($totalBalance) {
                $percentage = (float) $jar->percentage;
                $balance = (float) $jar->balance;

                // allocated is how much of the totalBalance corresponds to this jar's percentage
                $allocated = $totalBalance > 0 ? round(($percentage / 100) * $totalBalance, 2) : 0.0;

                return [
                    'id' => $jar->id,
                    'key' => $jar->name,
                    'label' => $jar->full_name ?? $jar->name,
                    'percentage' => $percentage,
                    'balance' => $balance,
                    'allocated' => $allocated,
                ];
            })->toArray();

            // Validate percentage sum for UI feedback (not required, but helpful)
            $percentSum = array_sum(array_column($jarConfigs, 'percentage'));
            $jarMeta = [
                'percent_sum' => $percentSum,
                'percent_sum_valid' => abs($percentSum - 100) < 0.001,
            ];

            // determine applied range if not explicitly provided
            $appliedRange = $range;
            if (!$appliedRange) {
                if ($start === $now->toDateString() && $end === $now->toDateString()) {
                    $appliedRange = 'day';
                } elseif ($start === $now->copy()->firstOfMonth()->toDateString() && $end === $now->copy()->endOfMonth()->toDateString()) {
                    $appliedRange = 'month';
                } elseif ($start === $now->copy()->firstOfYear()->toDateString() && $end === $now->copy()->endOfYear()->toDateString()) {
                    $appliedRange = 'year';
                } else {
                    $appliedRange = null;
                }
            }

            // debug:

            // Build filters to ensure mutual exclusivity between 'range' and 'start/end'
            $filters = $appliedRange
                ? ['range' => $appliedRange]
                : ['start' => $start, 'end' => $end];

            // Final guard (in case logic above changes), canonicalize filters
            $filters = $this->canonicalizeFilters($filters);

            return $this->success([
                'summary' => [
                    'total_balance' => $totalBalance,
                    'total_income' => $totalIncome,
                    'total_outcome' => $totalOutcome,
                ],
                'jars' => $jarConfigs,
                'jar_meta' => $jarMeta,
                'filters' => $filters,
            ], 'Dashboard loaded');
        } catch (Exception $e) {
            return $this->error('Dashboard load error: ' . $e->getMessage(), 500);
        }
    }
}
