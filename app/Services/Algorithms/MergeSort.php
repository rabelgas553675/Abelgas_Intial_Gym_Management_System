<?php

namespace App\Services\Algorithms;

/**
 * MergeSort — DSA Implementation
 *
 * Provides O(n log n) stable sort over in-memory arrays of objects/arrays.
 * Used by MemberController (sort members) and PaymentController (sort payments).
 *
 * "Stable" means equal elements keep their original relative order,
 * which matters when sorting paginated records by multiple criteria.
 */
class MergeSort
{
    /**
     * Sort an array of objects or associative arrays by a given field.
     *
     * @param  array  $items      The collection to sort
     * @param  string $field      The field/property key to sort by
     * @param  string $direction  'asc' or 'desc'
     * @return array              Sorted array (new copy, original unchanged)
     */
    public static function sortBy(array $items, string $field, string $direction = 'asc'): array
    {
        if (count($items) <= 1) {
            return $items;
        }

        $sorted = self::mergeSort($items, $field);

        return $direction === 'desc' ? array_reverse($sorted) : $sorted;
    }

    /**
     * Sort by multiple fields in priority order.
     * e.g. sortByMultiple($items, [['field' => 'name', 'dir' => 'asc'], ...])
     *
     * @param  array $items
     * @param  array $criteria  [['field' => string, 'dir' => 'asc'|'desc'], ...]
     * @return array
     */
    public static function sortByMultiple(array $items, array $criteria): array
    {
        if (count($items) <= 1 || empty($criteria)) {
            return $items;
        }

        return self::mergeSortMulti($items, $criteria);
    }

    // ────────────────────────────────────────────────────────────────
    //  Core recursive merge sort — single field
    // ────────────────────────────────────────────────────────────────

    private static function mergeSort(array $items, string $field): array
    {
        $n = count($items);
        if ($n <= 1) {
            return $items;
        }

        $mid   = intdiv($n, 2);
        $left  = self::mergeSort(array_slice($items, 0, $mid), $field);
        $right = self::mergeSort(array_slice($items, $mid), $field);

        return self::merge($left, $right, $field);
    }

    private static function merge(array $left, array $right, string $field): array
    {
        $result = [];
        $i      = 0;
        $j      = 0;

        while ($i < count($left) && $j < count($right)) {
            $lVal = self::getField($left[$i], $field);
            $rVal = self::getField($right[$j], $field);

            // Use string comparison for dates/strings, numeric for numbers
            $cmp = is_numeric($lVal) && is_numeric($rVal)
                ? $lVal <=> $rVal
                : strcmp((string) $lVal, (string) $rVal);

            if ($cmp <= 0) {
                $result[] = $left[$i++];
            } else {
                $result[] = $right[$j++];
            }
        }

        while ($i < count($left))  { $result[] = $left[$i++]; }
        while ($j < count($right)) { $result[] = $right[$j++]; }

        return $result;
    }

    // ────────────────────────────────────────────────────────────────
    //  Core recursive merge sort — multi-field
    // ────────────────────────────────────────────────────────────────

    private static function mergeSortMulti(array $items, array $criteria): array
    {
        $n = count($items);
        if ($n <= 1) {
            return $items;
        }

        $mid   = intdiv($n, 2);
        $left  = self::mergeSortMulti(array_slice($items, 0, $mid), $criteria);
        $right = self::mergeSortMulti(array_slice($items, $mid), $criteria);

        return self::mergeMulti($left, $right, $criteria);
    }

    private static function mergeMulti(array $left, array $right, array $criteria): array
    {
        $result = [];
        $i      = 0;
        $j      = 0;

        while ($i < count($left) && $j < count($right)) {
            $cmp = 0;

            foreach ($criteria as $criterion) {
                $field = $criterion['field'];
                $dir   = $criterion['dir'] ?? 'asc';
                $lVal  = self::getField($left[$i], $field);
                $rVal  = self::getField($right[$j], $field);

                $cmp = is_numeric($lVal) && is_numeric($rVal)
                    ? $lVal <=> $rVal
                    : strcmp((string) $lVal, (string) $rVal);

                if ($dir === 'desc') {
                    $cmp = -$cmp;
                }

                if ($cmp !== 0) {
                    break;
                }
            }

            if ($cmp <= 0) {
                $result[] = $left[$i++];
            } else {
                $result[] = $right[$j++];
            }
        }

        while ($i < count($left))  { $result[] = $left[$i++]; }
        while ($j < count($right)) { $result[] = $right[$j++]; }

        return $result;
    }

    // ────────────────────────────────────────────────────────────────
    //  Utility
    // ────────────────────────────────────────────────────────────────

    private static function getField(mixed $item, string $field): mixed
    {
        if (is_array($item)) {
            return $item[$field] ?? '';
        }
        return $item->{$field} ?? '';
    }
}