<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Ports\Out\Note\NoteCorrectionHistoryReaderPort;
use Illuminate\Support\Facades\DB;
use JsonException;

final class DatabaseNoteCorrectionHistoryReaderAdapter implements NoteCorrectionHistoryReaderPort
{
    public function findLatestNoteCorrections(string $noteId, int $limit = 10): array
    {
        $events = DB::table('note_mutation_events')
            ->where('note_id', trim($noteId))
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get(['id', 'mutation_type', 'actor_id', 'reason', 'occurred_at'])
            ->all();

        $snapshots = $this->snapshotMap(array_map(static fn (object $row): string => (string) $row->id, $events));

        return array_map(fn (object $row): array => $this->mapRow($row, $snapshots[(string) $row->id] ?? []), $events);
    }

    private function mapRow(object $row, array $snapshots): array
    {
        $before = is_array($snapshots['before'] ?? null) ? $snapshots['before'] : [];
        $after = is_array($snapshots['after'] ?? null) ? $snapshots['after'] : [];
        $meta = is_array($after['meta'] ?? null) ? $after['meta'] : (is_array($before['meta'] ?? null) ? $before['meta'] : []);

        return [
            'event_label' => (string) $row->mutation_type === 'paid_service_only_work_item_corrected'
                ? 'Correction Nominal Service'
                : 'Correction Status Work Item',
            'created_at' => (string) $row->occurred_at,
            'reason' => $row->reason !== null ? (string) $row->reason : null,
            'performed_by_actor_id' => $row->actor_id !== null ? (string) $row->actor_id : null,
            'target_status' => $meta['target_status'] ?? null,
            'refund_required_rupiah' => (int) ($meta['refund_required_rupiah'] ?? 0),
            'before_total_rupiah' => $before['note']['total_rupiah'] ?? null,
            'after_total_rupiah' => $after['note']['total_rupiah'] ?? null,
        ];
    }

    /**
     * @param list<string> $eventIds
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function snapshotMap(array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        $map = [];
        $rows = DB::table('note_mutation_snapshots')
            ->whereIn('note_mutation_event_id', $eventIds)
            ->get(['note_mutation_event_id', 'snapshot_kind', 'payload_json']);

        foreach ($rows as $row) {
            $map[(string) $row->note_mutation_event_id][(string) $row->snapshot_kind] = $this->decode((string) $row->payload_json);
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $json): array
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (JsonException) {
            return [];
        }
    }
}
