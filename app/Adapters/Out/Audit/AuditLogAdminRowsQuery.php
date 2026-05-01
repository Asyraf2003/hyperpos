<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

use Illuminate\Database\Query\Builder as QueryBuilder;

final class AuditLogAdminRowsQuery
{
    /**
     * @return iterable<int, object>
     */
    public function legacyRows(QueryBuilder $query, int $take): iterable
    {
        return (clone $query)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($take)
            ->get(['id', 'event', 'context', 'created_at']);
    }

    /**
     * @return iterable<int, object>
     */
    public function eventRows(QueryBuilder $query, int $take): iterable
    {
        return (clone $query)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit($take)
            ->get($this->eventColumns());
    }

    /**
     * @return list<string>
     */
    private function eventColumns(): array
    {
        return [
            'id',
            'bounded_context',
            'aggregate_type',
            'aggregate_id',
            'event_name',
            'actor_id',
            'actor_role',
            'reason',
            'source_channel',
            'metadata_json',
            'occurred_at',
        ];
    }
}
