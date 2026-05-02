<?php

namespace App\Services\Algorithms;

/**
 * GraphManager — DSA Implementation
 *
 * Directed graph using an adjacency list representation.
 * Supports BFS/DFS traversal for instructor → member relationship mapping.
 *
 * Used in: InstructorController::dashboard() and showMember()
 */
class GraphManager
{
    /**
     * Adjacency list: node => [neighbor nodes]
     */
    protected array $adjacency = [];

    /**
     * Store member objects keyed by member node string for fast lookup.
     */
    protected array $memberObjects = [];

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
            $instructorNode = 'instructor_' . $member->instructor_id;
            $memberNode     = 'member_'     . $member->id;

            // Ensure both nodes exist
            if (!isset($instance->adjacency[$instructorNode])) {
                $instance->adjacency[$instructorNode] = [];
            }
            if (!isset($instance->adjacency[$memberNode])) {
                $instance->adjacency[$memberNode] = [];
            }

            // Directed edge: instructor → member
            if (!in_array($memberNode, $instance->adjacency[$instructorNode])) {
                $instance->adjacency[$instructorNode][] = $memberNode;
            }

            // Store the member object for retrieval in bfsData()
            $instance->memberObjects[$memberNode] = $member;
        }

        return $instance;
    }

    // ────────────────────────────────────────────────────────────────
    //  BFS Traversal
    // ────────────────────────────────────────────────────────────────

    /**
     * BFS from this instructor's node.
     * Returns the actual Member model objects (not just IDs).
     *
     * Used in: InstructorController::dashboard()
     *
     * @param  int $instructorId
     * @return array  Array of Member models assigned to this instructor
     */
    public function bfsData(int $instructorId): array
    {
        $startNode = 'instructor_' . $instructorId;

        if (!isset($this->adjacency[$startNode])) {
            return [];
        }

        $visited = [];
        $queue   = [$startNode];
        $members = [];

        while (!empty($queue)) {
            $current = array_shift($queue);

            if (in_array($current, $visited)) {
                continue;
            }

            $visited[] = $current;

            // If this node is a member node, collect the object
            if (str_starts_with($current, 'member_') && isset($this->memberObjects[$current])) {
                $members[] = $this->memberObjects[$current];
            }

            foreach ($this->adjacency[$current] ?? [] as $neighbor) {
                if (!in_array($neighbor, $visited)) {
                    $queue[] = $neighbor;
                }
            }
        }

        return $members;
    }

    // ────────────────────────────────────────────────────────────────
    //  DFS Reachability
    // ────────────────────────────────────────────────────────────────

    /**
     * DFS check: is a member reachable from a given instructor?
     * Used to verify instructor ownership before granting access.
     *
     * Used in: InstructorController::showMember()
     *
     * @param  int $instructorId
     * @param  int $memberId
     * @return bool
     */
    public function isReachable(int $instructorId, int $memberId): bool
    {
        $startNode  = 'instructor_' . $instructorId;
        $targetNode = 'member_'     . $memberId;

        if (!isset($this->adjacency[$startNode])) {
            return false;
        }

        $visited = [];
        $stack   = [$startNode];

        while (!empty($stack)) {
            $current = array_pop($stack);

            if ($current === $targetNode) {
                return true;
            }

            if (in_array($current, $visited)) {
                continue;
            }

            $visited[] = $current;

            foreach ($this->adjacency[$current] ?? [] as $neighbor) {
                if (!in_array($neighbor, $visited)) {
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
     * = number of direct member edges from this instructor.
     *
     * Used in: InstructorController::dashboard()
     *
     * @param  int $instructorId
     * @return int
     */
    public function degree(int $instructorId): int
    {
        $node = 'instructor_' . $instructorId;
        return count($this->adjacency[$node] ?? []);
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