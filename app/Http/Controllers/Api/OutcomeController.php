<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\BuildsFinancialQuery;
use App\Http\Controllers\Concerns\PreservesFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\OutcomeStoreRequest;
use App\Http\Requests\OutcomeUpdateRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Jar;
use App\Models\Outcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutcomeController extends Controller
{
    use PreservesFilters;
    use BuildsFinancialQuery;
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        // 1. take user from rq
        $user = $request->user();

        // 2. base query
        $query = outcome::where('user_id', $user->id);

        // default route
        if (!$request->filled('range') && !$request->filled('start') && !$request->filled('end') && !$request->filled('page')) {
            $request->merge(['range' => 'day']);
        }

        // 3-6. apply common filters (range, date, search, sort) via trait
        $query = $this->applyfinancialfilters(
            $query,
            $request,
            ['category', 'description'],  // searchable columns for outcomes
            'date',  // default sort by
            'desc'  // default sort direction
        );

        // 7. pagination
        $perpage = (int) $request->input('per_page', 15);
        $outcomes = $query->with('jar:id,name')->paginate($perpage)->withquerystring();

        // 8. add jar_label to each outcome before transformation
        $outcomes->getcollection()->transform(function ($outcomes) {
            return [
                'id' => $outcomes->id,
                'date' => $outcomes->date,
                'category' => $outcomes->category,
                'description' => $outcomes->description,
                'amount' => $outcomes->amount,
                'formatted_amount' => number_format(
                    $outcomes->amount,
                    0,
                    ',',
                    '.',
                ) . ' ₫',
                'jar_id' => $outcomes->jar_id,
                'jar_label' => $outcomes->jar->name ?? 'none',
            ];
        });

        // 10. get user's jars for the dropdown in outcomemodal
        $jars = $user->jars()->get(['id', 'name']);

        // 11. return inertia page with props outcomes(paginator) + filters + jars
        /* return inertia::render('outcomes', [ */
        /* 'outcomes' => $outcomes, */
        /* 'jars' => $jars, */
        /* 'filters' => $this->canonicalizefilters( */
        /* $request->only(['range', 'start', 'end', 'search', 'sort_by', 'sort_dir', 'page', 'per_page']) */
        /* ), */
        /* ]); */

        return $this->success([
            'outcomes' => $outcomes,
            'jars' => $jars,
            'filters' => $this->canonicalizefilters(
                $request->only(['range', 'start', 'end', 'search', 'sort_by', 'sort_dir', 'page', 'per_page'])
            ),
        ], 'Outcomes loaded successfully');
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

            return $this->created(['outcome' => $outcome], 'Outcome added successfully');
        } catch (\RuntimeException $e) {
            /* $filters = $this->extractFiltersFromReferer($request) ?: ['range' => 'day', 'page' => 1]; */
            /* return redirect()->route('outcomes', $filters)->with('error', $e->getMessage()); */
            return $this->error($e->getMessage(), 400);
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
     * Display the specified resource.
     */
    public function show(Outcome $outcome)
    {
        // 1. Authorize: Check xem khoản chi này có phải của ông đang login không
        if ($outcome->user_id !== auth()->id()) {
            return $this->error('You do not have permission to do this', 403);
        }

        // 2. Eager load: Lấy luôn thông tin hũ liên quan
        $outcome->load('jar:id,name');

        // 3. Transform data: Format lại cho React dùng cho sướng
        $data = [
            'id' => $outcome->id,
            'date' => $outcome->date,
            'category' => $outcome->category,
            'description' => $outcome->description,
            'amount' => (float) $outcome->amount,
            'formatted_amount' => number_format($outcome->amount, 0, ',', '.') . ' ₫',
            'jar' => [
                'id' => $outcome->jar->id ?? null,
                'name' => $outcome->jar->name ?? 'None',
            ],
            'created_at' => $outcome->created_at->format('Y-m-d H:i:s'),
        ];

        return $this->success($data, 'Outcome details loaded');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OutcomeUpdateRequest $request, Outcome $outcome)
    {
        // 1. Ensure the outcome belongs to the authenticated user
        if ($outcome->user_id !== $request->user()->id) {
            /* abort(403, 'Unauthorized action.'); */
            return $this->error('You do not have permission to do this', 403);
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

            return $this->success(null, 'Outcome updated successfully');
        } catch (\RuntimeException $e) {
            /* return redirect()->route('outcomes', $filters)->with('error', $e->getMessage()); */
            return $this->error('Error when updating outcome: ' . $e->getMessage(), 400);
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
            return $this->error('You do not have permission to do this', 403);
        }

        // 2. Refund jar (if any) then delete outcome within a transaction
        DB::transaction(function () use ($outcome) {
            if (!empty($outcome->jar_id)) {
                Jar::where('id', $outcome->jar_id)->lockForUpdate()->increment('balance', (int) round((float) $outcome->amount));
            }
            $outcome->delete();
        });

        // 3. Extract filters from referer for preserving state

        // 4. Redirect back with preserved filters
        /* return redirect()->route('outcomes', $filters)->with('success', 'Deleted outcome!'); */
        return $this->success(null, 'Outcome deleted successfully');
    }
}
