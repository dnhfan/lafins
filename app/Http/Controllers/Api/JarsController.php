<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Jar;
use Illuminate\Http\JsonResponse as HttpJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class JarsController extends Controller
{
    use ApiResponse;
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show jars configuration page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // The database stores jar type in the 'name' column (NEC, FFA, ...).
        // The frontend expects objects with 'label' and 'key' fields. Map accordingly.
        $jars = Jar::where('user_id', $user->id)
            ->orderBy('id')
            ->get(['id', 'name', 'percentage', 'balance']);

        $payload = $jars->map(function ($jar) {
            return [
                'id' => $jar->id,
                // Use short code for display (NEC, FFA, ...)
                'key' => $jar->name,
                'label' => $jar->full_name ?? $jar->name,
                'percentage' => (float) $jar->percentage,
                'balance' => (float) $jar->balance,
            ];
        })->toArray();

        /* return Inertia::render('jarconfigs', [ */
        /* 'jars' => $payload, */
        /* ]); */

        return $this->success([
            'jars' => $payload
        ], 'Jars loaded successfully');
    }

    /**
     * Bulk update percentages for multiple jars
     * Expects payload: { percentages: { <jarId>: <percent>, ... } }
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $user = $request->user();

        $payload = $request->validate([
            'percentages' => ['required', 'array'],
            'percentages.*' => ['numeric', 'min:0', 'max:100'],
        ]);

        $percentages = $payload['percentages'];

        // Do updates and redistribution in a transaction
        DB::transaction(function () use ($user, $percentages) {
            // Load jars for user
            $jars = Jar::where('user_id', $user->id)->orderBy('id')->get();

            // Update percentages in-memory map
            $jarMap = [];
            foreach ($jars as $jar) {
                if (array_key_exists($jar->id, $percentages)) {
                    $jar->percentage = (float) round($percentages[$jar->id], 2);
                }
                // ensure percentage is numeric
                $jarMap[$jar->id] = $jar;
            }

            // Persist updated percentages
            foreach ($jarMap as $jar) {
                $jar->save();
            }

            // Redistribute balances according to new percentages
            $total = (float) Jar::where('user_id', $user->id)->sum('balance');

            // If total is zero, just ensure balances are zero (no-op)
            if ($total <= 0) {
                foreach ($jarMap as $jar) {
                    Jar::where('id', $jar->id)->update(['balance' => 0]);
                }
                return;
            }

            // Calculate target balances with cent precision (2 decimals)
            $distributedTotal = 0.0;
            $shares = [];
            foreach ($jarMap as $jar) {
                $pct = (float) $jar->percentage / 100.0;
                $raw = $total * $pct;
                // floor to cents
                $share = floor($raw * 100) / 100.0;
                $shares[$jar->id] = $share;
                $distributedTotal += $share;
            }

            // Remainder due to rounding
            $remainder = round($total - $distributedTotal, 2);
            if ($remainder > 0) {
                // assign remainder to jar with highest percentage (tie-breaker: lowest id)
                $target = collect($jarMap)->sortByDesc('percentage')->first();
                $shares[$target->id] += $remainder;
            }

            // Apply new balances
            foreach ($shares as $jarId => $balance) {
                Jar::where('id', $jarId)->update(['balance' => $balance]);
            }
        });

        /* return redirect()->back()->with('success', 'Jar percentages updated and balances redistributed'); */
        return $this->success(null, 'Jar percentages updated and balances redistributed');
    }

    /**
     * Delete all jars for the current user (dangerous)
     */
    public function deleteAll(Request $request): HttpJsonResponse
    {
        $user = $request->user();

        // Use DB transaction to ensure atomicity
        \DB::transaction(function () use ($user) {
            // 1) delete incomes and outcomes for user
            \App\Models\Income::where('user_id', $user->id)->delete();
            \App\Models\Outcome::where('user_id', $user->id)->delete();

            // 2) reset jars to configured defaults (percent) and zero balances
            $defaults = config('jars.defaults', []);
            foreach ($defaults as $name => $percent) {
                Jar::updateOrCreate(
                    ['user_id' => $user->id, 'name' => $name],
                    ['percentage' => $percent, 'balance' => 0]
                );
            }
        });

        /* return redirect()->route('jarconfigs')->with('success', 'All financial data reset to defaults'); */
        return $this->success(null, 'Jar data reset to defaults');
    }

    /**
     * Reset jar percentages to configured defaults for the current user (no financial deletions)
     */
    public function reset(Request $request): JsonResponse
    {
        $user = $request->user();

        $defaults = config('jars.defaults', []);

        // Update percentages and redistribute balances according to defaults
        DB::transaction(function () use ($user, $defaults) {
            // Ensure jars exist and set default percentages
            foreach ($defaults as $name => $percent) {
                Jar::updateOrCreate(
                    ['user_id' => $user->id, 'name' => $name],
                    ['percentage' => (float) round($percent, 2)]
                );
            }

            // Load jars for redistribution
            $jars = Jar::where('user_id', $user->id)->orderBy('id')->get();

            // Calculate total balance across jars
            $total = (float) $jars->sum('balance');

            if ($total <= 0) {
                // set balances to zero (safe) and return
                foreach ($jars as $jar) {
                    Jar::where('id', $jar->id)->update(['balance' => 0]);
                }
                return;
            }

            // Compute target balances (cent precision)
            $distributedTotal = 0.0;
            $shares = [];
            foreach ($jars as $jar) {
                $pct = (float) $jar->percentage / 100.0;
                $raw = $total * $pct;
                $share = floor($raw * 100) / 100.0;  // floor to cents
                $shares[$jar->id] = $share;
                $distributedTotal += $share;
            }

            // Assign remainder to highest percentage jar
            $remainder = round($total - $distributedTotal, 2);
            if ($remainder > 0) {
                $target = $jars->sortByDesc('percentage')->first();
                $shares[$target->id] += $remainder;
            }

            // Apply new balances
            foreach ($shares as $jarId => $balance) {
                Jar::where('id', $jarId)->update(['balance' => $balance]);
            }
        });

        /* return redirect()->route('jarconfigs')->with('success', 'Jar percentages reset to defaults and balances redistributed'); */
        return $this->success(null, 'Jar percentages reset to defaults and balances redistributed');
    }
}
