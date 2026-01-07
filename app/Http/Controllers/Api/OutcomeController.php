<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\BuildsFinancialQuery;
use App\Http\Controllers\Concerns\PreservesFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\OutcomeStoreRequest;
use App\Http\Requests\OutcomeUpdateRequest;
use App\Models\Jar;
use App\Models\Outcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OutcomeController extends Controller
{
    use PreservesFilters;
    use BuildsFinancialQuery;

    public function __construct()
    {
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
        if (!$request->filled('range') && !$request->filled('start') && !$request->filled('end') && !$request->filled('page')) {
            /* return redirect()->route('outcomes', ['range' => 'day', 'page' => 1]); */
            $request->merge(['range' 'day']);
        }

        // 3-6. Apply common filters (range, date, search, sort) via trait
        $query = $this->applyFinancialFilters(
            $query,
            $request,
            ['category', 'description'],  // searchable columns for outcomes
            'date',  // default sort by
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
        /* return Inertia::render('outcomes', [ */
        /* 'outcomes' => $outcomes, */
        /* 'jars' => $jars, */
        /* 'filters' => $this->canonicalizeFilters( */
        /* $request->only(['range', 'start', 'end', 'search', 'sort_by', 'sort_dir', 'page', 'per_page']) */
        /* ), */
        /* ]); */

        return response()->json([
            'outcomes' => $outcomes,
            'jars' => $jars,
            'filters' => $this->canonicalizeFilters(
                $request->only(['range', 'start', 'end', 'search', 'sort_by', 'sort_dir', 'page', 'per_page'])
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
        $user = $request->user();

        // 2. Add user_id to the data
        $validated['user_id'] = $user->id;

        // 3. Create the outcome and adjust jar balance inside a transaction
        try {
            $outcome = DB::transaction(function () use ($validated, $user) {
                // If a jar is selected, ensure it belongs to user and has enough balance
                if (!empty($validated['jar_id'])) {
                    $jar = Jar::where('id', $validated['jar_id'])
                        ->where('user_id', $user->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$jar) {
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

                return Outcome::create($validated);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Added outcome!',
                'data' => $outcome,
            ], 201);
        } catch (\RuntimeException $e) {
            /* $filters = $this->extractFiltersFromReferer($request) ?: ['range' => 'day', 'page' => 1]; */
            /* return redirect()->route('outcomes', $filters)->with('error', $e->getMessage()); */
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        /* // 4. Extract filters from referer for preserving state */
        /* $filters = $this->extractFiltersFromReferer($request); */
        /* if (empty($filters)) { */
        /* $filters = ['range' => 'day', 'page' => 1]; */
        /* } */
        /* // 5. Redirect back with preserved filters */
        /* return redirect()->route('outcomes', $filters)->with('success', 'Added outcome!'); */
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OutcomeUpdateRequest $request, Outcome $outcome)
    {
        // 1. Ensure the outcome belongs to the authenticated user
        if ($outcome->user_id !== $request->user()->id) {
            /* abort(403, 'Unauthorized action.'); */
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to do this!',
            ],403);
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
                    if (!$newJar) {
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

            return response()->json([
                'status' => 'success',
                'message' => 'Updated outcomes'
            ]);
        } catch (\RuntimeException $e) {
            $filters = $this->extractFiltersFromReferer($request) ?: ['range' => 'day', 'page' => 1];
            /* return redirect()->route('outcomes', $filters)->with('error', $e->getMessage()); */
            return response()->json([
                'status' => 'error',
                'message' => 'Error when update outcomes: ' . $e->getMessage()
            ],400);
        }

        /* // 4. Extract filters from referer for preserving state */
        /* $filters = $this->extractFiltersFromReferer($request); */
        /**/
        /* // 5. Redirect back with preserved filters */
        /* return redirect()->route('outcomes', $filters)->with('success', 'Updated outcome!'); */
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Outcome $outcome)
    {
        // 1. Ensure the outcome belongs to the authenticated user
        if ($outcome->user_id !== $request->user()->id) {
            /* abort(403, 'Unauthorized action.'); */
            return response()->json([
                'status' => 'error',
                'message'=> 'You do not have permission to do this!',
            ],403);
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
        /* return redirect()->route('outcomes', $filters)->with('success', 'Deleted outcome!'); */
        return response()->json([
            'status' => 'success',
            'message' => 'Deleted outcomes!',
        ]);
    }
}
