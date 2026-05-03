<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

use App\Ports\Out\AuditLogReaderPort;
use App\Ports\Out\Shared\PaginatedResult;
use Illuminate\Support\Facades\DB;

final class DatabaseAuditLogReaderAdapter implements AuditLogReaderPort
{
    private const EVENTS = [
        'paid_work_item_status_corrected',
        'paid_service_only_work_item_corrected',
    ];

    public function __construct(
        private readonly AuditLogAdminRowMapper $adminRowMapper = new AuditLogAdminRowMapper(),
        private readonly AuditLogAdminListQuery $adminListQuery = new AuditLogAdminListQuery(),
    ) {
    }

    public function findLatestNoteCorrections(string $noteId, int $limit = 10): array
    {
        $rows = DB::table('audit_logs')
            ->whereIn('event', self::EVENTS)
            ->orderByDesc('id')
            ->limit(max($limit * 5, 20))
            ->get(['event', 'context', 'created_at']);

        $entries = [];

        foreach ($rows as $row) {
            $context = json_decode((string) $row->context, true);

            if (! is_array($context) || ($context['note_id'] ?? null) !== $noteId) {
                continue;
            }

            $entries[] = [
                'event' => (string) $row->event,
                'context' => $context,
                'created_at' => (string) $row->created_at,
            ];

            if (count($entries) >= $limit) {
                break;
            }
        }

        return $entries;
    }

    public function listForAdmin(string $search = '', int $perPage = 20): PaginatedResult
    {
        $paginator = $this->adminListQuery->paginate($search, $perPage, $this->adminRowMapper);

        return new PaginatedResult(
            items: $paginator->items(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }
}
