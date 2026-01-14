<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JarResource;
use App\Http\Traits\ApiResponse;
use App\Models\Jar;
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
            ->get();

        /* return Inertia::render('jarconfigs', [ */
        /* 'jars' => $payload, */
        /* ]); */

        return $this->success([
            'jars' => JarResource::collection($jars),
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

        // checking percentage before calculate
        if (abs(array_sum($percentages) - 100) > 0.001) {
            return $this->error('Total percentage must be exactly 100%', 422, ['percentages' => 'Total percentage must be 100%']);
        }

        $updatedJars = Jar::where('user_id', $user->id)->whereIn('id', array_keys($percentages))->get();

        if ($updatedJars->count() !== count($percentages)) {
            return $this->error('Unauthorized or invalid jar IDs', 422);
        }

        // Do updates and redistribution in a transaction
        DB::transaction(function () use ($user, $percentages, $updatedJars) {
            // Update percentages in-memory map
            $jarMap = [];
            foreach ($updatedJars as $jar) {
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

            // Take all the Jars
            $allUserJars = Jar::where('user_id', $user->id)->orderBy('id')->get();

            $this->redistributedBalances($allUserJars);
        });

        /* return redirect()->back()->with('success', 'Jar percentages updated and balances redistributed'); */
        return $this->success(null, 'Jar percentages updated and balances redistributed');
    }

    /**
     * Delete all jars for the current user (dangerous)
     */
    public function deleteAll(Request $request): JsonResponse
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

            $this->redistributedBalances($jars);
        });

        /* return redirect()->route('jarconfigs')->with('success', 'Jar percentages reset to defaults and balances redistributed'); */
        return $this->success(null, 'Jar percentages reset to defaults and balances redistributed');
    }

    public function redistributedBalances($jars): void
    {
        // 1. take total
        $totalBalance = (float) $jars->sum('balance');

        // edge case: no money => reset
        if ($totalBalance <= 0) {
            Jar::WhereIn('id', $jars->pluck('id'))->update(['balance' => 0]);
            return;
        }

        $shares = [];
        $distributedTotal = 0;

        // 3. calcu temp money
        foreach ($jars as $jar) {
            $pct = (float) $jar->percentage / 100;

            $raw = $totalBalance * $pct;
            $share = floor($raw * 100) / 100;

            $shares[$jar->id] = $share;

            $distributedTotal += $shares[$jar->id];
        }

        // 4. handle remainder
        $remainder = round($totalBalance - $distributedTotal, 2);

        if ($remainder > 0) {
            $targetJar = $jars->sortByDesc('percentage')->first();

            if ($targetJar) {
                $shares[$targetJar->id] += $remainder;
            }
        }

        // 5. update in db
        foreach ($shares as $jarId => $balance) {
            Jar::where('id', $jarId)->update(['balance' => $balance]);
        }
    }
}
