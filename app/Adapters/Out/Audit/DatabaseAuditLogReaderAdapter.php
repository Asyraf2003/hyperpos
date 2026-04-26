<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

use App\Ports\Out\AuditLogReaderPort;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class DatabaseAuditLogReaderAdapter implements AuditLogReaderPort
{
    private const EVENTS = [
        'paid_work_item_status_corrected',
        'paid_service_only_work_item_corrected',
    ];

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

    public function listForAdmin(string $search = '', int $perPage = 20): LengthAwarePaginator
    {
        $normalizedSearch = trim($search);
        $safePerPage = max(1, min($perPage, 100));

        $query = DB::table('audit_logs')
            ->select(['id', 'event', 'context', 'created_at'])
            ->orderByDesc('id');

        if ($normalizedSearch !== '') {
            $like = '%' . $normalizedSearch . '%';

            $query->where(function (QueryBuilder $query) use ($like): void {
                $query
                    ->where('event', 'like', $like)
                    ->orWhere('context', 'like', $like);
            });
        }

        /** @var LengthAwarePaginator<int, object> $paginator */
        $paginator = $query->paginate($safePerPage)->withQueryString();

        return $paginator->through(function (object $row): array {
            $context = json_decode((string) $row->context, true);

            if (! is_array($context)) {
                $context = [];
            }

            $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            return [
                'id' => (int) $row->id,
                'event' => (string) $row->event,
                'reason' => $this->resolveReason($context),
                'context' => $context,
                'context_json' => is_string($contextJson) ? $contextJson : '{}',
                'created_at' => (string) $row->created_at,
            ];
        });
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveReason(array $context): string
    {
        foreach (['reason', 'alasan', 'void_reason', 'correction_reason', 'note', 'notes'] as $key) {
            $value = $context[$key] ?? null;

            if (is_scalar($value) && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return '-';
    }
}
