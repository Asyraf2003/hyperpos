<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class AuditLogAdminListQuery
{
    public function __construct(
        private readonly AuditLogAdminQueryFilters $filters = new AuditLogAdminQueryFilters(),
        private readonly AuditLogAdminEntrySorter $sorter = new AuditLogAdminEntrySorter(),
        private readonly AuditLogAdminRowsQuery $rows = new AuditLogAdminRowsQuery(),
    ) {
    }

    public function paginate(string $search, int $perPage, AuditLogAdminRowMapper $mapper): LengthAwarePaginator
    {
        $safePerPage = max(1, min($perPage, 100));
        $page = max(1, LengthAwarePaginator::resolveCurrentPage());
        $take = $page * $safePerPage;
        $legacyQuery = $this->legacyQuery(trim($search));
        $eventQuery = $this->eventQuery(trim($search));
        $total = (clone $legacyQuery)->count() + (clone $eventQuery)->count();
        $entries = $this->entries($legacyQuery, $eventQuery, $take, $mapper);

        return (new LengthAwarePaginator(
            array_slice($entries, ($page - 1) * $safePerPage, $safePerPage),
            $total,
            $safePerPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()],
        ))->withQueryString();
    }

    private function legacyQuery(string $search): QueryBuilder
    {
        return $this->filters->applyLegacy(DB::table('audit_logs'), $search);
    }

    private function eventQuery(string $search): QueryBuilder
    {
        return $this->filters->applyEvent(DB::table('audit_events'), $search);
    }

    /**
     * @return list<array{id:string,source:string,event:string,reason:string,actor_id:?string,actor_role:?string,entity_type:?string,entity_id:?string,bounded_context:?string,context:array<string,mixed>,context_json:string,created_at:string}>
     */
    private function entries(
        QueryBuilder $legacyQuery,
        QueryBuilder $eventQuery,
        int $take,
        AuditLogAdminRowMapper $mapper,
    ): array {
        $entries = [];

        foreach ($this->rows->legacyRows($legacyQuery, $take) as $row) {
            $entries[] = $mapper->mapLegacy($row);
        }

        foreach ($this->rows->eventRows($eventQuery, $take) as $row) {
            $entries[] = $mapper->mapEvent($row);
        }

        $this->sorter->sortNewestFirst($entries);

        return $entries;
    }
}
