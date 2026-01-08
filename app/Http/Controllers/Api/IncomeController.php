<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\BuildsFinancialQuery as BuildsFinancialQuery;
use App\Http\Controllers\Concerns\PreservesFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeStoreRequest;
use App\Http\Requests\IncomeUpdateRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Income;
use App\Models\IncomeJarSplit;
use App\Models\Jar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class IncomeController extends Controller
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
    public function index(Request $request)
    {
        try {
            // 1. take user from rq
            $user = $request->user();
            // 2. base query
            $query = Income::where('user_id', $user->id);

            // default route
            if (!$request->filled('range') && !$request->filled('start') && !$request->filled('end') && !$request->filled('page')) {
                $request->merge(['range', 'day']);
            }

            // 3-6. Apply common filters (range, date, search, sort) via trait
            $query = $this->applyFinancialFilters(
                $query,
                $request,
                ['source', 'description'],  // searchable columns
                'date',  // default sort by
                'desc'  // default sort direction
            );

            // 7. pagination
            $perPage = (int) $request->input('per_page', 15);
            $incomes = $query->paginate($perPage)->withQueryString();

            // 8. transform collection -> Send only needed fields + formatted_amount
            $this->transformFinancialCollection($incomes, ['id', 'date', 'source', 'description', 'amount']);

            // 9. Return json with props incomes(paginator) + filters
            return $this->success([
                'incomes' => $incomes,
                'filters' => $this->canonicalizeFilters(
                    $request->only(['range', 'start', 'end', 'search', 'sort_by', 'sort_dir', 'page', 'per_page'])
                ),
            ], 'Getted incomes');
        } catch (Exception $e) {
            return $this->error('Get incomes error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomeStoreRequest $request)
    {
        try {
            // 1. take data from request + validate
            $data = $request->validated();

            // 2. int casting 'amount'
            $data['amount'] = (int) round($data['amount']);

            // 3. create income
            // Use DB transaction to ensure jars update consistently with the income
            $income = null;
            DB::transaction(function () use ($data, &$income, $request) {
                $income = Income::create([
                    'user_id' => auth()->id(),
                    'date' => $data['date'],
                    'source' => $data['source'],
                    'description' => $data['description'] ?? null,
                    'amount' => $data['amount'],
                ]);

                // 4. split amount into jars according to each jar's percentage for the current user
                $userId = auth()->id();
                $amount = $data['amount'];

                // Load jars for user
                $jars = Jar::where('user_id', $userId)->get();

                // If no jars found, skip splitting
                if ($jars->isEmpty()) {
                    return;
                }

                // Calculate distributed amounts and update jar balances
                // To avoid rounding issues we accumulate distributed and put remainder into the largest-percentage jar
                $distributedTotal = 0;
                $shares = [];
                foreach ($jars as $jar) {
                    $share = (int) floor($amount * ($jar->percentage / 100));
                    $shares[$jar->id] = $share;
                    $distributedTotal += $share;
                }

                // Remainder due to rounding
                $remainder = $amount - $distributedTotal;
                if ($remainder > 0) {
                    // Find jar with highest percentage (tie-breaker: lowest id)
                    $target = $jars->sortByDesc('percentage')->first();
                    $shares[$target->id] += $remainder;
                }

                // Apply increments and store splits
                foreach ($jars as $jar) {
                    $add = $shares[$jar->id] ?? 0;
                    if ($add > 0) {
                        // Use query to avoid race conditions on model instance
                        Jar::where('id', $jar->id)->increment('balance', $add);
                        // Persist split
                        IncomeJarSplit::create([
                            'income_id' => $income->id,
                            'jar_id' => $jar->id,
                            'amount' => $add,
                        ]);
                    }
                }
            });

            // Redirect back with current filters (from referer) to keep view state; fallback to sensible defaults
            $filters = $this->extractFiltersFromReferer($request);
            if (empty($filters)) {
                $filters = ['range' => 'day', 'page' => 1];
            }
            /* return redirect()->route('incomes', $filters)->with('success', 'added income!'); */
            return $this->created([
                'income' => $income,
                'filters' => $filters,
            ], 'Added income');
        } catch (ValidationException $e) {
            return $this->error('Data invalid', 422, $e->errors());
        } catch (Exception $e) {
            return $this->error('Add income error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncomeUpdateRequest $request, Income $income)
    {
        try {
            // 1. Authorize: ensure the income belongs to current user
            if ($income->user_id !== auth()->id()) {
                return $this->error('You do not have the permission to do this', 403);
            }

            $data = $request->validated();

            // 2. int casting 'amount'
            $data['amount'] = (int) round($data['amount']);

            $income->update($data);

            $filters = $this->extractFiltersFromReferer($request);
            if (empty($filters)) {
                $filters = ['range' => 'day', 'page' => 1];
            }

            /* return redirect()->route('incomes', $filters)->with('success', 'Updated income!'); */

            return $this->success([
                'income' => $income,
                'filters' => $filters,
            ], 'Updated income');
        } catch (ValidationException $e) {
            return $this->error('Data invalid', 422, $e->errors());
        } catch (Exception $e) {
            return $this->error('Update income error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income)
    {
        // 1. Authorize: ensure the income belongs to current user
        if ($income->user_id !== auth()->id()) {
            return $this->error('You do not have permission to do this', 403);
        }

        // 2. Before deleting, fetch splits and ensure reversing them won't make any jar negative
        $splits = IncomeJarSplit::where('income_id', $income->id)->get();

        // If no splits exist, safe to delete
        if ($splits->isEmpty()) {
            $income->delete();
            $filters = $this->extractFiltersFromReferer(request());
            if (empty($filters)) {
                $filters = ['range' => 'day', 'page' => 1];
            }
            /* return redirect()->route('incomes', $filters)->with('success', 'Deleted income'); */
            return $this->success(null, 'Deleted income');
        }

        // Check viability: for each split, the jar must have >= split.amount to subtract
        $jarIds = $splits->pluck('jar_id')->unique();
        $jars = Jar::WhereIn('id', $jarIds)->get()->keyBy('id');

        $violations = [];
        foreach ($splits as $split) {
            $jar = $jars->get($split->jar_id);

            if (!$jar) {
                $violations[] = "Jar #{$split->jar_id} not found";
                continue;
            }

            if ((float) $jar->balance < (float) $split->amount) {
                $violations[] = "Jar {$jar->name} has insufficient balance to reverse allocation.";
            }
        }

        if (!empty($violations)) {
            /* $filters = $this->extractFiltersFromReferer(request()) ?: ['range' => 'day', 'page' => 1]; */
            /* return redirect()->route('incomes', $filters)->with('error', implode(' ', $violations)); */
            return $this->error(implode(' ', $violations));
        }

        // All good: reverse splits and delete inside transaction
        try {
            DB::transaction(function () use ($splits, $income) {
                foreach ($splits as $split) {
                    Jar::where('id', $split->jar_id)->decrement('balance', (float) $split->amount);
                }
                // delete splits
                IncomeJarSplit::where('income_id', $income->id)->delete();
                // delete income
                $income->delete();
            });

            return $this->success(null, 'Deleted and updated jar split');
        } catch (\Exception $e) {
            return $this->error('Delete income err: ', $e->getMessage(), 500);
        }
        // 3. Redirect back to list with success message, preserving current filters from referer
        /* $filters = $this->extractFiltersFromReferer(request()); */
        /* if (empty($filters)) { */
        /* $filters = ['range' => 'day', 'page' => 1]; */
        /* } */
        /* return redirect()->route('incomes', $filters)->with('success', 'Deleted income'); */
    }

    public function show(Income $income)
    {
        // 1. Authorize
        if ($income->user_id != auth()->id()) {
            return $this->error('You do not have permission to do this', 403);
        }

        // 2. load relationship 'splits' and jar infor
        $income->load(['splits.jar:id,name']);

        // 3. transform data for frontend
        $details = [
            'id' => $income->id,
            'date' => $income->date,
            'source' => $income->source,
            'description' => $income->description,
            'amounts' => $income->amount,
            'formatted_amount' => number_format(
                $income->amount,
                0,
                ',',
                '.',
            ) . ' ₫',
            'splits' => $income->splits->map(function ($splits) {
                return [
                    'jar_id' => $splits->jar_id,
                    'jar_name' => $splits->jar->name ?? 'N/A',
                    'amounts' => (float) $splits->amount,
                ];
            }),
            'created_at' => $income->created_at->format('Y-m-d H:i:s'),
        ];

        return $this->success($details, 'Income details loaded');
    }
}
