<?php

namespace App\Services\Algorithms;

/**
 * BinarySearch — DSA Implementation
 *
 * Provides binary search over sorted in-memory arrays.
 *
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │  COMPLEXITY NOTES (per consultation feedback)                       │
 * │                                                                     │
 * │  searchByField()  — PARTIAL / PREFIX MATCH                         │
 * │    • Uses binary search to locate the nearest alphabetical anchor   │
 * │      position: O(log n) to find the region.                        │
 * │    • Then expands left/right from that anchor to collect all items  │
 * │      whose field contains the query string.                         │
 * │    • If the anchor binary search misses (non-contiguous matches),   │
 * │      it falls back to a single linear O(n) scan.                   │
 * │    • Overall: O(log n) best case, O(n) worst case.                 │
 * │    • This is intentional — partial name search cannot be           │
 * │      guaranteed O(log n) without a prefix tree / inverted index.   │
 * │    • Suitable for: member name search, receipt prefix search.      │
 * │                                                                     │
 * │  findExact()  — EXACT MATCH — pure O(log n)                        │
 * │    • Classic binary search with no fallback.                        │
 * │    • Requires the array to be sorted ascending by $field.          │
 * │    • Suitable for: lookup by ID, exact receipt number, email.      │
 * └─────────────────────────────────────────────────────────────────────┘
 *
 * The array MUST be sorted by the target key before calling any method.
 * Use MergeSort::sortBy() to sort first, then call these methods.
 */
class BinarySearch
{
    /**
     * Search a sorted array by a field using partial (contains) matching.
     *
     * Strategy:
     *   1. Binary search to find an anchor index where the field value
     *      is closest alphabetically to the query — O(log n).
     *   2. Expand left and right from the anchor while the field still
     *      contains the query — O(k) where k = number of matches.
     *   3. If no anchor is found by binary search (matches are scattered
     *      non-contiguously), fall back to a single linear scan — O(n).
     *
     * Complexity: O(log n + k) typical, O(n) worst case (non-contiguous).
     * Note: Pure O(log n) is not achievable for partial-match search on
     * a flat sorted array. This is the best attainable with this structure.
     *
     * @param  array  $sorted  Array pre-sorted ascending by $field (use MergeSort first)
     * @param  string $field   Key to search on (e.g. 'name', 'email', 'receipt_number')
     * @param  string $query   Search term — partial/prefix match, case-insensitive
     * @return array           All items where $field contains $query
     */
    public static function searchByField(array $sorted, string $field, string $query): array
    {
        if (empty($sorted) || $query === '') {
            return $sorted;
        }

        $query   = strtolower(trim($query));
        $results = [];

        // Step 1 — Binary search to anchor near matching region
        $anchor = self::findAnchorIndex($sorted, $field, $query);

        if ($anchor === -1) {
            return [];
        }

        // Step 2 — Expand left from anchor while field still matches
        $lo = $anchor;
        while ($lo > 0 && self::fieldContains($sorted[$lo - 1], $field, $query)) {
            $lo--;
        }

        // Step 3 — Expand right from anchor while field still matches
        $hi = $anchor;
        $n  = count($sorted);
        while ($hi < $n - 1 && self::fieldContains($sorted[$hi + 1], $field, $query)) {
            $hi++;
        }

        for ($i = $lo; $i <= $hi; $i++) {
            $results[] = $sorted[$i];
        }

        return $results;
    }

    /**
     * Exact match binary search — pure O(log n).
     *
     * Finds a single item whose $field exactly equals $value.
     * No fallback; if the value is not present, returns null.
     *
     * Use this method when you need a guaranteed O(log n) lookup,
     * e.g. finding a payment by exact receipt_number, or a member by ID.
     *
     * Complexity: O(log n) — no linear fallback.
     *
     * @param  array  $sorted  Array pre-sorted ascending by $field
     * @param  string $field   Key to search on
     * @param  mixed  $value   Exact value to match (case-sensitive string comparison)
     * @return mixed|null      The matching item, or null if not found
     */
    public static function findExact(array $sorted, string $field, mixed $value): mixed
    {
        $lo = 0;
        $hi = count($sorted) - 1;

        while ($lo <= $hi) {
            $mid    = intdiv($lo + $hi, 2);
            $midVal = self::getField($sorted[$mid], $field);
            $cmp    = strcmp((string) $midVal, (string) $value);

            if ($cmp === 0) {
                return $sorted[$mid];
            } elseif ($cmp < 0) {
                $lo = $mid + 1;
            } else {
                $hi = $mid - 1;
            }
        }

        return null;
    }

    /**
     * Binary search to locate the first index where $field contains $query.
     *
     * Uses binary comparison to navigate toward the alphabetical region
     * where a match is most likely. If binary search finds no anchor,
     * falls back to a single linear scan (O(n)) as a safety net for
     * non-contiguous match distributions.
     *
     * @internal
     */
    private static function findAnchorIndex(array $sorted, string $field, string $query): int
    {
        $lo     = 0;
        $hi     = count($sorted) - 1;
        $anchor = -1;

        while ($lo <= $hi) {
            $mid    = intdiv($lo + $hi, 2);
            $midVal = strtolower((string) self::getField($sorted[$mid], $field));

            if (str_contains($midVal, $query)) {
                // Found a match — record it and search left for earlier match
                $anchor = $mid;
                $hi     = $mid - 1;
            } elseif ($midVal < $query) {
                $lo = $mid + 1;
            } else {
                $hi = $mid - 1;
            }
        }

        // Fallback: if binary navigation missed (scattered matches),
        // perform one linear scan. Complexity degrades to O(n) in this case.
        if ($anchor === -1) {
            foreach ($sorted as $i => $item) {
                if (self::fieldContains($item, $field, $query)) {
                    return $i;
                }
            }
        }

        return $anchor;
    }

    /**
     * Extract a field value from an object or associative array.
     *
     * @internal
     */
    private static function getField(mixed $item, string $field): mixed
    {
        if (is_array($item)) {
            return $item[$field] ?? '';
        }
        return $item->{$field} ?? '';
    }

    /**
     * Check whether an item's field value contains the query string (case-insensitive).
     *
     * @internal
     */
    private static function fieldContains(mixed $item, string $field, string $query): bool
    {
        return str_contains(
            strtolower((string) self::getField($item, $field)),
            $query
        );
    }
}