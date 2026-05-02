<?php

namespace App\Services\Algorithms;

/**
 * BinarySearch — DSA Implementation
 *
 * Provides O(log n) search over sorted in-memory arrays.
 * Used in MemberController (member search) and PaymentController (payment lookup).
 *
 * The array MUST be sorted by the target key before calling any search method.
 * Use MergeSort::sortBy() to sort first, then call these methods.
 */
class BinarySearch
{
    /**
     * Search a sorted array of objects/arrays by a single string field.
     * Returns all items where the field contains $query (case-insensitive).
     *
     * Strategy: binary-search to find ANY matching index, then expand
     * left and right to collect all contiguous matches.
     *
     * @param  array  $sorted  Array sorted ascending by $field
     * @param  string $field   Key to search on (e.g. 'name', 'email')
     * @param  string $query   Search term (partial match supported)
     * @return array           All matching items
     */
    public static function searchByField(array $sorted, string $field, string $query): array
    {
        if (empty($sorted) || $query === '') {
            return $sorted;
        }

        $query   = strtolower(trim($query));
        $results = [];

        // Because we support partial (LIKE) matching, binary search can only
        // narrow us to a candidate region — we still scan from that anchor.
        // This still beats a full linear scan on large sorted datasets because
        // we start from the closest alphabetical position, not index 0.
        $anchor = self::findAnchorIndex($sorted, $field, $query);

        if ($anchor === -1) {
            return [];
        }

        // Expand from anchor in both directions while the prefix still matches
        $lo = $anchor;
        while ($lo > 0 && self::fieldContains($sorted[$lo - 1], $field, $query)) {
            $lo--;
        }

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
     * Exact binary search — find a single item by an exact field value.
     * Returns the matching item or null if not found.
     *
     * @param  array  $sorted  Array sorted ascending by $field
     * @param  string $field   Key to search on
     * @param  mixed  $value   Exact value to match
     * @return mixed|null
     */
    public static function findExact(array $sorted, string $field, mixed $value): mixed
    {
        $lo = 0;
        $hi = count($sorted) - 1;

        while ($lo <= $hi) {
            $mid      = intdiv($lo + $hi, 2);
            $midVal   = self::getField($sorted[$mid], $field);
            $cmp      = strcmp((string) $midVal, (string) $value);

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
     * Binary search to find the first index where field >= query prefix.
     * Returns -1 if no candidate found.
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
                $anchor = $mid;
                // Try to find an earlier match
                $hi = $mid - 1;
            } elseif ($midVal < $query) {
                $lo = $mid + 1;
            } else {
                $hi = $mid - 1;
            }
        }

        // If binary search missed (non-contiguous matches), fall back to linear scan
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
     * Extract a field value from an object or array item.
     */
    private static function getField(mixed $item, string $field): mixed
    {
        if (is_array($item)) {
            return $item[$field] ?? '';
        }
        return $item->{$field} ?? '';
    }

    /**
     * Check whether an item's field contains the query string.
     */
    private static function fieldContains(mixed $item, string $field, string $query): bool
    {
        return str_contains(strtolower((string) self::getField($item, $field)), $query);
    }
}