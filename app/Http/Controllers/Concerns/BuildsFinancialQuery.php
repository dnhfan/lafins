<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait BuildsFinancialQuery
{
    /**
     * Build a financial query with common filters: range, date, search, sort
     *
     * @param Builder|\Illuminate\Database\Eloquent\Model $query Base query (already filtered by user_id)
     * @param Request $request
     * @param array $searchableColumns Columns to search (default: ['source', 'description'] for income, ['category', 'description'] for outcome)
     * @param string $defaultSortBy Default sort column (default: 'date')
     * @param string $defaultSortDir Default sort direction (default: 'desc')
     * @return Builder
     */
    protected function applyFinancialFilters(
        $query,
        Request $request,
        array $searchableColumns = ['source', 'description'],
        string $defaultSortBy = 'date',
        string $defaultSortDir = 'desc'
    ): Builder {
        // 1. Range preset (day/month/year)
        if ($request->filled('range')) {
            $now = Carbon::now();
            $start = null;
            $end = null;

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

            if (isset($start, $end)) {
                $query->whereBetween('date', [$start, $end]);
            }
        }

        // 2. Custom TimeRange (start/end) only when no preset range is provided
        if (!$request->filled('range') && ($request->filled('start') || $request->filled('end'))) {
            $start = $request->input('start') ?? '1974-01-01';
            $end = $request->input('end') ?? Carbon::today()->toDateString();

            // Protection: end date cannot be greater than today
            $end = min($end, Carbon::today()->toDateString());
            if ($start > $end) {
                $start = $end; // if start > end, filter only end day
            }
            $query->whereBetween('date', [$start, $end]);
        }

        // 3. Search (server-side) across searchable columns
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term, $searchableColumns) {
                foreach ($searchableColumns as $index => $column) {
                    if ($index === 0) {
                        $q->where($column, 'like', "%{$term}%");
                    } else {
                        $q->orWhere($column, 'like', "%{$term}%");
                    }
                }
            });
        }

        // 4. Sorting (only allow safe columns)
        $allowedSorts = ['date', 'amount'];
        $allowedDirs = ['asc', 'desc'];

        $sortBy = $request->input('sort_by', $defaultSortBy);
        $sortDir = $request->input('sort_dir', $defaultSortDir);

        // Ensure only safe columns/directions
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = $defaultSortBy;
        }
        if (!in_array($sortDir, $allowedDirs)) {
            $sortDir = $defaultSortDir;
        }

        $query->orderBy($sortBy, $sortDir);

        return $query;
    }

    /**
     * Transform paginated collection to include formatted_amount
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param array $fields Fields to include in transformation
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function transformFinancialCollection($paginator, array $fields = ['id', 'date', 'source', 'description', 'amount'])
    {
        $paginator->getCollection()->transform(function ($item) use ($fields) {
            $result = [];
            
            foreach ($fields as $field) {
                if ($field === 'date' && isset($item->date)) {
                    $result['date'] = $item->date ? $item->date->format('Y-m-d') : null;
                } else {
                    $result[$field] = $item->{$field} ?? null;
                }
            }

            // Always add formatted_amount
            $result['formatted_amount'] = number_format((float) ($item->amount ?? 0), 0, ',', '.') . ' ₫';

            return $result;
        });

        return $paginator;
    }
}
