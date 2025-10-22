<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeStoreRequest;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Extract current filters from the Referer URL so redirects can preserve them.
     */
    private function extractFiltersFromReferer(Request $request): array
    {
        $referer = $request->headers->get('referer');
        if (!$referer) return [];

        $queryString = parse_url($referer, PHP_URL_QUERY);
        if (!$queryString) return [];

        parse_str($queryString, $params);
        if (!is_array($params)) return [];

        $allowed = ['range', 'start', 'end', 'search', 'sort_by', 'sort_dir', 'page', 'per_page'];
        return array_intersect_key($params, array_flip($allowed));
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

        // If user navigates to /incomes without any range/start/end/page params,
        // redirect them to the default view: range=day and page=1.
        // This provides a consistent initial state and avoids showing an empty
        // unfiltered list.
        if (! $request->filled('range') && ! $request->filled('start') && ! $request->filled('end') && ! $request->filled('page')) {
            return redirect()->route('incomes', ['range' => 'day', 'page' => 1]);
        }

        // 3. Range preset (day/month/year)
        if ($request->filled('range')) {
            // if range isset
            $now = Carbon::now();
            if ($request->range === 'day') {
                $start = $now->startOfDay()->toDateString();
                $end = $now->endOfDay()->toDateString();
            } elseif ($request->range === 'month') {
                $start = $now->startOfMonth()->toDateString();
                $end = $now->endOfMonth()->toDateString();
            } elseif ($request->range === 'year') {
                $start = $now->startOfYear()->toDateString();
                $end = $now->endOfYear()->toDateString();
            }
            // not have preset
            if (isset($start, $end)) {
                $query->whereBetween('date', [$start, $end]);
            }
        }
        
        // 4. using TimeRangeModel (start/end) only when no preset range is provided
        if (! $request->filled('range') && ($request->filled('start') || $request->filled('end'))) {
            $start = $request->input('start') ?? '1974-01-01';
            $end = $request->input('end') ?? Carbon::today()->toDateString();

            // protected
            $end = min($end, Carbon::today()->toDateString()); // ngày end không được lớn hơn hôm hôm nay
            if($start > $end) $start = $end; // start > end -> fillter only end day
            $query->whereBetween('date', [$start, $end]);
        }

        // 5. search (server-side): support searching by `source` or `description`
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('source', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // 6. sorting (only allow safe collums) 
        $allowedSorts = ['date', 'amount'];
        $allowedDirs = ['asc', 'desc'];

        $sortBy = $request->input('sort_by', 'time');  // default: time
        $sortDir = $request->input('sort_dir', 'desc'); // default: newest/highest first

        // ensure only safe columns/directions
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'date';
        }
        if (!in_array($sortDir, $allowedDirs)) {
            $sortDir = 'desc';
        }

        $query->orderBy($sortBy, $sortDir);

        // 7. pagination
        // số record mỗi trang
        $perPage = (int) $request->input('per_page', 15);
        
        // apply a sensible default sort (newest first) and keep query string on paginator
        $incomes = $query->latest()->paginate($perPage)->withQueryString();
        // note: cuối cùng sau 7749 cái lọc thì query mới hoàn thiện và được gán vào incomes

        /*
        - $query ở đây là query builder đã được build từ đầu (có thể đã có where, filter, sort...).
        - Hàm paginate($perPage) là Eloquent pagination, Laravel sẽ tự động:
            1. Lấy $perPage bản ghi đầu tiên (ví dụ 15 bản ghi).
            2. Tính tổng số bản ghi (SELECT count(*)).
            3. Tạo các thuộc tính tiện ích như:
                $incomes->currentPage() – trang hiện tại.
                $incomes->lastPage() – tổng số trang.
                $incomes->total() – tổng số bản ghi.
                $incomes->nextPageUrl() – URL của trang kế tiếp.
                $incomes->previousPageUrl() – URL của trang trước đó.
            4. Trả về một object LengthAwarePaginator, chứ không phải chỉ là một collection.
         */
    
        // 8. transform collection -> Gửi chỉ field cần + formatted_amount
        $incomes->getCollection()->transform(function ($inc) {
            return [
                'id' => $inc->id,
                // make date handling defensive in case it's null/not a Carbon instance
                'date' => $inc->date ? $inc->date->format('Y-m-d') : null,
                'source' => $inc->source,
                'description' => $inc->description,
                'amount' => $inc->amount,
                // cast amount to float before formatting (casts may return string)
                'formatted_amount' => number_format((float) $inc->amount, 0, ',', '.') . ' ₫',
            ];
        });

        // 9. Return Inertia page với props incomes(paginator) + fillters
        return Inertia::render('incomes', [
            'incomes' => $incomes,
            // align with other controllers which expose 'filters' (plural)
            'filters' => $request->only(['range', 'start', 'end', 'search', 'sort_by','sort_dir', 'page', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
