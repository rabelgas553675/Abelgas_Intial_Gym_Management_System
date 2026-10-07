<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when someone tries to permanently delete a member who already has
 * payment / transaction / attendance (or other) history.
 */
class MemberHasHistoryException extends RuntimeException
{
    /**
     * @param  array<string,int>  $blockers  e.g. ['payment' => 3, 'attendance' => 12]
     */
    public function __construct(private array $blockers = [])
    {
        parent::__construct(self::buildMessage($blockers));
    }

    /** @return array<string,int> */
    public function blockers(): array
    {
        return $this->blockers;
    }

    /**
     * Friendly, human-readable message. When the exact records are unknown
     * (e.g. the database foreign key stopped the delete) a generic list is used.
     *
     * @param  array<string,int>  $blockers
     */
    public static function buildMessage(array $blockers = []): string
    {
        $labels = array_keys($blockers);

        if ($labels === []) {
            $list = 'payment, transaction or attendance';
        } else {
            $last = array_pop($labels);
            $list = $labels ? implode(', ', $labels) . ' and ' . $last : $last;
        }

        return "This member cannot be deleted because they have existing {$list} records. "
             . 'Please deactivate or suspend the member instead.';
    }
}