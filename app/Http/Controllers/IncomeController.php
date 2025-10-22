<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeStoreRequest;
use App\Http\Requests\IncomeUpdateRequest;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Concerns\PreservesFilters;
use App\Http\Controllers\Concerns\BuildsFinancialQuery;

class IncomeController extends Controller
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
        $query = Income::where('user_id', $user->id);

        // default route
        if (! $request->filled('range') && ! $request->filled('start') && ! $request->filled('end') && ! $request->filled('page')) {
            return redirect()->route('incomes', ['range' => 'day', 'page' => 1]);
        }

        // 3-6. Apply common filters (range, date, search, sort) via trait
        $query = $this->applyFinancialFilters(
            $query,
            $request,
            ['source', 'description'], // searchable columns
            'date', // default sort by
            'desc'  // default sort direction
        );

        // 7. pagination
        $perPage = (int) $request->input('per_page', 15);
        $incomes = $query->paginate($perPage)->withQueryString();

        // 8. transform collection -> Send only needed fields + formatted_amount
        $this->transformFinancialCollection($incomes, ['id', 'date', 'source', 'description', 'amount']);

        // 9. Return Inertia page with props incomes(paginator) + filters
        return Inertia::render('incomes', [
            'incomes' => $incomes,
            'filters' => $this->canonicalizeFilters(
                $request->only(['range', 'start', 'end', 'search', 'sort_by','sort_dir', 'page', 'per_page'])
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomeStoreRequest $request)
    {
        // 1. take data from request + validate
        $data = $request->validated();

        // 2. int casting 'amount'
        $data['amount'] = (int) round($data['amount']);
        
        // 3. create income
        $income = Income::create([
            'user_id' => auth()->id(),
            'date' => $data['date'],
            'source' => $data['source'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],            
        ]);

        // Redirect back with current filters (from referer) to keep view state; fallback to sensible defaults
        $filters = $this->extractFiltersFromReferer($request);
        if (empty($filters)) {
            $filters = ['range' => 'day', 'page' => 1];
        }
        return redirect()->route('incomes', $filters)->with('success', 'added income!');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(IncomeUpdateRequest $request, Income $income)
    {
        // 1. Authorize: ensure the income belongs to current user
        if ($income->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validated();

        // 2. int casting 'amount'
        $data['amount'] = (int) round($data['amount']);

        $income->update($data);
        
        $filters = $this->extractFiltersFromReferer($request);
        if (empty($filters)) {
            $filters = ['range' => 'day', 'page' => 1];
        }

        return redirect()->route('incomes', $filters) -> with('success', 'Updated income!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income)
    {
        // 1. Authorize: ensure the income belongs to current user
        if ($income->user_id !== auth()->id()) {
            abort(403);
        }

        // 2. Delete
        $income->delete();

        // 3. Redirect back to list with success message, preserving current filters from referer
        $filters = $this->extractFiltersFromReferer(request());
        if (empty($filters)) {
            $filters = ['range' => 'day', 'page' => 1];
        }
        return redirect()->route('incomes', $filters)->with('success', 'Deleted income');
    }
}
