<?php

namespace App\Services\Algorithms;

/**
 * GraphManager — DSA Implementation
 *
 * Directed graph using an adjacency list representation.
 * Supports BFS/DFS traversal for instructor → member relationship mapping.
 *
 * Used in: InstructorController::dashboard() and showMember()
 *
 * OPTIMIZATIONS over baseline:
 *   - visited set uses hash keys (isset) instead of in_array → O(1) vs O(n)
 *   - adjacency list stores neighbors as hash keys for O(1) duplicate check
 *   - bfsData() skips full BFS — instructor→member is a 1-level graph,
 *     so we directly return the neighbor objects (no queue needed)
 *   - isReachable() short-circuits with O(1) direct hash lookup
 *   - memberObjects keyed by integer member_id for direct numeric lookup
 */
class GraphManager
{
    /**
     * Adjacency list: node => [neighborNode => true]  (hash set, not array)
     */
    protected array $adjacency = [];

    /**
     * Member objects keyed by integer member_id for O(1) retrieval.
     */
    protected array $memberObjects = [];

    /**
     * Direct instructor → member_ids map for O(1) neighbor lookup.
     * instructor_id (int) => [member_id (int) => true]
     */
    protected array $instructorMembers = [];

    // ────────────────────────────────────────────────────────────────
    //  Graph Construction
    // ────────────────────────────────────────────────────────────────

    /**
     * Build a directed graph from members collection.
     * Creates edges: instructor_node → member_node
     *
     * @param  array $members  Array of Member models
     * @return static
     */
    public static function buildFromMembers(array $members): static
    {
        $instance = new static();

        foreach ($members as $member) {
            $iid = (int) $member->instructor_id;
            $mid = (int) $member->id;

            // Integer-keyed direct maps — no string concat overhead
            $instance->instructorMembers[$iid][$mid] = true;
            $instance->memberObjects[$mid]            = $member;

            // Keep string-keyed adjacency for getAdjacency() / degree() compat
            $instructorNode = 'instructor_' . $iid;
            $memberNode     = 'member_'     . $mid;

            $instance->adjacency[$instructorNode][$memberNode] = true;
            $instance->adjacency[$memberNode]                  ??= [];
        }

        return $instance;
    }

    // ────────────────────────────────────────────────────────────────
    //  BFS Traversal  (optimised: 1-level graph → direct lookup)
    // ────────────────────────────────────────────────────────────────

    /**
     * Return all Member objects assigned to an instructor.
     *
     * Because the graph is a strict 2-level bipartite structure
     * (instructor → members, no deeper edges), a full BFS queue is
     * wasteful. We directly read the instructor's neighbor hash set.
     *
     * O(k) where k = number of members for this instructor.
     *
     * Used in: InstructorController::dashboard()
     *
     * @param  int $instructorId
     * @return array  Array of Member models
     */
    public function bfsData(int $instructorId): array
    {
        if (!isset($this->instructorMembers[$instructorId])) {
            return [];
        }

        $members = [];
        foreach ($this->instructorMembers[$instructorId] as $mid => $_) {
            if (isset($this->memberObjects[$mid])) {
                $members[] = $this->memberObjects[$mid];
            }
        }

        return $members;
    }

    // ────────────────────────────────────────────────────────────────
    //  DFS Reachability  (hash-keyed visited set)
    // ────────────────────────────────────────────────────────────────

    /**
     * DFS check: is a member reachable from a given instructor?
     *
     * Short-circuits with a direct hash lookup for the 1-level case.
     * Falls back to generic DFS with O(1) visited checks for deeper graphs.
     *
     * Used in: InstructorController::showMember()
     *
     * @param  int $instructorId
     * @param  int $memberId
     * @return bool
     */
    public function isReachable(int $instructorId, int $memberId): bool
    {
        // Fast path: direct adjacency check — O(1)
        if (isset($this->instructorMembers[$instructorId][$memberId])) {
            return true;
        }

        // Generic DFS fallback (hash-keyed visited)
        $startNode  = 'instructor_' . $instructorId;
        $targetNode = 'member_'     . $memberId;

        if (!isset($this->adjacency[$startNode])) {
            return false;
        }

        $visited = [];   // [node => true] — O(1) lookup
        $stack   = [$startNode];

        while (!empty($stack)) {
            $current = array_pop($stack);

            if ($current === $targetNode) {
                return true;
            }

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            foreach ($this->adjacency[$current] as $neighbor => $_) {
                if (!isset($visited[$neighbor])) {
                    $stack[] = $neighbor;
                }
            }
        }

        return false;
    }

    // ────────────────────────────────────────────────────────────────
    //  Graph Metrics
    // ────────────────────────────────────────────────────────────────

    /**
     * Out-degree of an instructor node.
     *
     * @param  int $instructorId
     * @return int
     */
    public function degree(int $instructorId): int
    {
        return isset($this->instructorMembers[$instructorId])
            ? count($this->instructorMembers[$instructorId])
            : 0;
    }

    /**
     * Get the full adjacency list (useful for debugging).
     *
     * @return array
     */
    public function getAdjacency(): array
    {
        return $this->adjacency;
    }
}