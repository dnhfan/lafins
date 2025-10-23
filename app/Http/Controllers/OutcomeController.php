<?php

namespace App\Http\Controllers;

use App\Models\Outcome;
use App\Models\Jar;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\PreservesFilters;
use App\Http\Controllers\Concerns\BuildsFinancialQuery;
use App\Http\Requests\OutcomeStoreRequest;
use App\Http\Requests\OutcomeUpdateRequest;

class OutcomeController extends Controller
{
    use PreservesFilters;
    use BuildsFinancialQuery;
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. take user from rq
        $user = $request->user();

        // 2. base query
        $query = Outcome::where('user_id', $user->id);

        // default route
        if (! $request->filled('range') && ! $request->filled('start') && ! $request->filled('end') && ! $request->filled('page')) {
            return redirect()->route('outcomes', ['range' => 'day', 'page' => 1]);
        }

        // 3-6. Apply common filters (range, date, search, sort) via trait
        $query = $this->applyFinancialFilters(
            $query,
            $request,
            ['category', 'description'], // searchable columns for outcomes
            'date', // default sort by
            'desc'  // default sort direction
        );

        // 7. pagination
        $perPage = (int) $request->input('per_page', 15);
        $outcomes = $query->with('jar')->paginate($perPage)->withQueryString();

        // 8. Add jar_label to each outcome BEFORE transformation
        $outcomes->getCollection()->transform(function ($outcome) {
            // Check if jar relationship exists and is loaded
            if ($outcome->jar_id && $outcome->jar) {
                // Use 'name' field from jar, not 'label'
                $outcome->jar_label = $outcome->jar->name ?? 'None';
            } else {
                $outcome->jar_label = 'None';
            }
            return $outcome;
        });

        // 9. transform collection -> Send only needed fields + formatted_amount + jar info
        $this->transformFinancialCollection($outcomes, ['id', 'date', 'category', 'description', 'amount', 'jar_id', 'jar_label']);

        // 10. Get user's jars for the dropdown in OutcomeModal
        $jars = $user->jars;

        // 11. Return Inertia page with props outcomes(paginator) + filters + jars
        return Inertia::render('outcomes', [
            'outcomes' => $outcomes,
            'jars' => $jars,
            'filters' => $this->canonicalizeFilters(
                $request->only(['range', 'start', 'end', 'search', 'sort_by','sort_dir', 'page', 'per_page'])
            ),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(OutcomeStoreRequest $request)
    {
        // 1. Get validated data from the OutcomeStoreRequest
        $validated = $request->validated();

        // 2. Add user_id to the data
        $validated['user_id'] = $request->user()->id;

        // 3. Create the outcome and adjust jar balance inside a transaction
        try {
            DB::transaction(function () use ($validated, &$created) {
                // If a jar is selected, ensure it belongs to user and has enough balance
                if (!empty($validated['jar_id'])) {
                    $jar = Jar::where('id', $validated['jar_id'])
                        ->where('user_id', $validated['user_id'])
                        ->lockForUpdate()
                        ->first();

                    if (! $jar) {
                        throw new \RuntimeException('Selected jar not found.');
                    }

                    $amount = (int) round($validated['amount']);
                    // balance is decimal, compare as numeric
                    if ((float) $jar->balance < $amount) {
                        throw new \RuntimeException('Insufficient balance in selected jar.');
                    }

                    // Decrement jar balance atomically
                    Jar::where('id', $jar->id)->decrement('balance', $amount);
                }

                $created = Outcome::create($validated);
            });
        } catch (\RuntimeException $e) {
            $filters = $this->extractFiltersFromReferer($request) ?: ['range' => 'day', 'page' => 1];
            return redirect()->route('outcomes', $filters)->with('error', $e->getMessage());
        }

        // 4. Extract filters from referer for preserving state
        $filters = $this->extractFiltersFromReferer($request);
        if (empty($filters)) {
            $filters = ['range' => 'day', 'page' => 1];
        } 
        // 5. Redirect back with preserved filters
        return redirect()->route('outcomes', $filters)->with('success', 'Added outcome!');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(OutcomeUpdateRequest $request, Outcome $outcome)
    {
        // 1. Ensure the outcome belongs to the authenticated user
        if ($outcome->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Get validated data from the OutcomeUpdateRequest
        $validated = $request->validated();

        // 3. Update the outcome and adjust jar balances accordingly
        try {
            DB::transaction(function () use ($outcome, $validated) {
                $userId = $outcome->user_id;
                $oldJarId = $outcome->jar_id;
                    $oldAmount = (int) round((float) $outcome->amount);

                // 1) Refund old jar if present
                if (!empty($oldJarId)) {
                    Jar::where('id', $oldJarId)->lockForUpdate()->increment('balance', $oldAmount);
                }

                // 2) If new jar specified, deduct new amount from it
                if (!empty($validated['jar_id'])) {
                    $newJar = Jar::where('id', $validated['jar_id'])
                        ->where('user_id', $userId)
                        ->lockForUpdate()
                        ->first();
                    if (! $newJar) {
                        throw new \RuntimeException('Selected jar not found.');
                    }
                    $newAmount = (int) round($validated['amount']);
                    if ((float) $newJar->balance < $newAmount) {
                        throw new \RuntimeException('Insufficient balance in selected jar.');
                    }
                    Jar::where('id', $newJar->id)->decrement('balance', $newAmount);
                }

                // 3) Finally update the outcome
                $outcome->update($validated);
            });
        } catch (\RuntimeException $e) {
            $filters = $this->extractFiltersFromReferer($request) ?: ['range' => 'day', 'page' => 1];
            return redirect()->route('outcomes', $filters)->with('error', $e->getMessage());
        }

        // 4. Extract filters from referer for preserving state
        $filters = $this->extractFiltersFromReferer($request);

        // 5. Redirect back with preserved filters
        return redirect()->route('outcomes', $filters)->with('success', 'Updated outcome!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Outcome $outcome)
    {
        // 1. Ensure the outcome belongs to the authenticated user
        if ($outcome->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Refund jar (if any) then delete outcome within a transaction
        DB::transaction(function () use ($outcome) {
                if (!empty($outcome->jar_id)) {
                    Jar::where('id', $outcome->jar_id)->lockForUpdate()->increment('balance', (int) round((float) $outcome->amount));
                }
            $outcome->delete();
        });

        // 3. Extract filters from referer for preserving state
        $filters = $this->extractFiltersFromReferer($request);

        // 4. Redirect back with preserved filters
        return redirect()->route('outcomes', $filters)->with('success', 'Deleted outcome!');
    }
}
