<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\PreservesFilters as PreservesFiltersTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\JarResource;
use App\Http\Traits\ApiResponse;
use App\Models\Income;
use App\Models\Jar;
use App\Models\Outcome;
use App\Services\DateRangeService;
use Illuminate\Http\Request;
use Exception;

/**
 * @group Dashboard
 *
 * API for dashboard summary and statistics
 */
class DashboardController extends Controller
{
    use PreservesFiltersTrait;
    use ApiResponse;

    /**
     * Get dashboard summary
     *
     * Retrieves financial summary including total balance, income, and outcome for a specified date range, along with jar configurations.
     *
     * @authenticated
     *
     * @queryParam range string Filter by date range (day, week, month, year). Example: month
     * @queryParam start string Filter start date (YYYY-MM-DD). Example: 2024-01-01
     * @queryParam end string Filter end date (YYYY-MM-DD). Example: 2024-12-31
     *
     * @response {
     *   "status": "success",
     *   "message": "Dashboard loaded",
     *   "data": {
     *     "summary": {
     *       "total_balance": 5000000,
     *       "total_income": 6000000,
     *       "total_outcome": 1000000
     *     },
     *     "jars": [
     *       {
     *         "id": 1,
     *         "name": "NEC",
     *         "percentage": 55,
     *         "balance": 2750000
     *       }
     *     ],
     *     "jar_meta": {
     *       "percent_sum": 100,
     *       "percent_sum_valid": true
     *     },
     *     "filters": {
     *       "range": "month"
     *     }
     *   }
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "Dashboard load error: ..."
     * }
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // using service for taking date
            $dates = DateRangeService::parse(
                $request->query('range'),
                $request->query('start'),
                $request->query('end'),
            );

            /** For Total money */

            // Totals for the range
            // Use the Income model's scopeBetweenDates() helper
            $totalIncome = (float) Income::where('user_id', $user->id)
                ->betweenDates($dates['start'], $dates['end'])
                ->sum('amount');

            $totalOutcome = (float) Outcome::where('user_id', $user->id)
                ->betweenDates($dates['start'], $dates['end'])
                ->sum('amount');

            // Total balance is sum of jars' balances for the user
            $totalBalance = (float) Jar::where('user_id', $user->id)->sum('balance');

            /** For Pie chart and jar list */

            // Load jars for this user and prepare configuration (percentage, balance, allocated amount)
            $jars = Jar::where('user_id', $user->id)
                ->orderBy('id')
                ->get(['id', 'name', 'percentage', 'balance']);

            $jarConfigs = JarResource::collection($jars)->additional(['totalBalance' => $totalBalance]);

            // Validate percentage sum for UI feedback (not required, but helpful)
            $percentSum = $jars->sum('percentage');

            $jarMeta = [
                'percent_sum' => $percentSum,
                'percent_sum_valid' => abs($percentSum - 100) < 0.001,
            ];

            // Final guard (in case logic above changes), canonicalize filters
            $filters = $this->canonicalizeFilters($dates);

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
