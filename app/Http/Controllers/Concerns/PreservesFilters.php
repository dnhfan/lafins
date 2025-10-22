<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait PreservesFilters
{
    /**
     * Ensure mutual exclusivity between 'range' and 'start/end'.
     * If 'range' exists, drop 'start' and 'end'. If either 'start' or 'end' exists, drop 'range'.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    protected function canonicalizeFilters(array $params): array
    {
        $hasRange = isset($params['range']) && $params['range'] !== null && $params['range'] !== '';
        $hasStartOrEnd = (isset($params["start"]) && $params["start"] !== null && $params["start"] !== '')
            || (isset($params["end"]) && $params["end"] !== null && $params["end"] !== '');

        if ($hasRange) {
            unset($params['start'], $params['end']);
        } elseif ($hasStartOrEnd) {
            unset($params['range']);
        }

        return $params;
    }

    /**
     * Extract current filters from the Referer URL so redirects can preserve them.
     *
     * @param Request $request
     * @param array<string> $allowedKeys Override allowed keys if needed
     * @return array<string, mixed>
     */
    protected function extractFiltersFromReferer(Request $request, array $allowedKeys = [
        'range', 'start', 'end', 'search', 'sort_by', 'sort_dir', 'page', 'per_page',
    ]): array {
        $referer = $request->headers->get('referer');
        if (!$referer) return [];

        $queryString = parse_url($referer, PHP_URL_QUERY);
        if (!$queryString) return [];

        parse_str($queryString, $params);
        if (!is_array($params)) return [];

        $filtered = array_intersect_key($params, array_flip($allowedKeys));
        return $this->canonicalizeFilters($filtered);
    }
}
