<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Jar;
use Illuminate\Http\RedirectResponse;

class JarsController extends Controller
{
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
                        'label' => $jar->name,
                        'percentage' => (float) $jar->percentage,
                        'balance' => (float) $jar->balance,
                    ];
            })->toArray();

            return Inertia::render('jarconfigs', [
                'jars' => $payload,
            ]);
    }

    /**
     * Update a single jar percentage
     */
    public function update(Request $request, Jar $jar): RedirectResponse
    {
        if ($jar->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

    // Preserve decimal precision to match DB (decimal(5,2))
    $jar->percentage = (float) round($data['percentage'], 2);
        $jar->save();

        return redirect()->back()->with('success', 'Jar updated');
    }

    /**
     * Bulk update percentages for multiple jars
     * Expects payload: { percentages: { <jarId>: <percent>, ... } }
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $user = $request->user();

        $payload = $request->validate([
            'percentages' => ['required', 'array'],
            'percentages.*' => ['numeric', 'min:0', 'max:100'],
        ]);

        $percentages = $payload['percentages'];

        foreach ($percentages as $jarId => $percent) {
            $jar = Jar::where('user_id', $user->id)->where('id', $jarId)->first();
            if ($jar) {
                // Preserve two decimal places
                $jar->percentage = (float) round($percent, 2);
                $jar->save();
            }
        }

        return redirect()->back()->with('success', 'Jar percentages updated');
    }

    /**
     * Delete all jars for the current user (dangerous)
     */
    public function deleteAll(Request $request): RedirectResponse
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

        return redirect()->route('jarconfigs')->with('success', 'All financial data reset to defaults');
    }

    /**
     * Reset jar percentages to configured defaults for the current user (no financial deletions)
     */
    public function reset(Request $request): RedirectResponse
    {
        $user = $request->user();

        $defaults = config('jars.defaults', []);

        foreach ($defaults as $name => $percent) {
            Jar::updateOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                ['percentage' => $percent]
            );
        }

        return redirect()->route('jarconfigs')->with('success', 'Jar percentages reset to defaults');
    }
}

