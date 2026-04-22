<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Core\Note\Revision\NoteRevision;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Ports\Out\Note\NoteRevisionReaderPort;
use App\Ports\Out\Note\NoteRevisionWriterPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class DbNoteRevisionRepository implements NoteRevisionReaderPort, NoteRevisionWriterPort
{
    public function findById(string $revisionId): ?NoteRevision
    {
        $normalized = trim($revisionId);

        if ($normalized === '') {
            return null;
        }

        $row = DB::table('note_revisions')->where('id', $normalized)->first();

        if ($row === null) {
            return null;
        }

        return $this->mapRevision($row);
    }

    public function findCurrentByRootId(string $noteRootId): ?NoteRevision
    {
        $normalized = trim($noteRootId);

        if ($normalized === '') {
            return null;
        }

        $currentRevisionId = DB::table('notes')
            ->where('id', $normalized)
            ->value('current_revision_id');

        if (! is_string($currentRevisionId) || trim($currentRevisionId) === '') {
            return null;
        }

        return $this->findById($currentRevisionId);
    }

    public function nextRevisionNumber(string $noteRootId): int
    {
        $normalized = trim($noteRootId);

        if ($normalized === '') {
            return 1;
        }

        $max = DB::table('note_revisions')
            ->where('note_root_id', $normalized)
            ->max('revision_number');

        return ((int) $max) + 1;
    }

    public function findTimelineByRootId(string $noteRootId, int $limit = 50): array
    {
        $normalized = trim($noteRootId);

        if ($normalized === '') {
            return [];
        }

        $rows = DB::table('note_revisions')
            ->where('note_root_id', $normalized)
            ->orderByDesc('revision_number')
            ->limit(max($limit, 1))
            ->get();

        $items = [];

        foreach ($rows as $row) {
            $items[] = $this->mapRevision($row);
        }

        return $items;
    }

    public function existsForRootId(string $noteRootId): bool
    {
        $normalized = trim($noteRootId);

        if ($normalized === '') {
            return false;
        }

        return DB::table('note_revisions')
            ->where('note_root_id', $normalized)
            ->exists();
    }

    public function create(NoteRevision $revision): void
    {
        DB::table('note_revisions')->insert([
            'id' => $revision->id(),
            'note_root_id' => $revision->noteRootId(),
            'revision_number' => $revision->revisionNumber(),
            'parent_revision_id' => $revision->parentRevisionId(),
            'created_by_actor_id' => $revision->createdByActorId(),
            'reason' => $revision->reason(),
            'customer_name' => $revision->customerName(),
            'customer_phone' => $revision->customerPhone(),
            'transaction_date' => $revision->transactionDate()->format('Y-m-d'),
            'grand_total_rupiah' => $revision->grandTotalRupiah(),
            'line_count' => $revision->lineCount(),
            'created_at' => $revision->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => null,
        ]);

        $lineRows = [];

        foreach ($revision->lines() as $line) {
            $lineRows[] = [
                'id' => $line->id(),
                'note_revision_id' => $line->noteRevisionId(),
                'work_item_root_id' => $line->workItemRootId(),
                'line_no' => $line->lineNo(),
                'transaction_type' => $line->transactionType(),
                'status' => $line->status(),
                'service_label' => $line->serviceLabel(),
                'service_price_rupiah' => $line->servicePriceRupiah(),
                'subtotal_rupiah' => $line->subtotalRupiah(),
                'payload' => $this->encodePayload($line->payload()),
                'created_at' => $revision->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => null,
            ];
        }

        if ($lineRows !== []) {
            DB::table('note_revision_lines')->insert($lineRows);
        }
    }

    public function setCurrentRevision(string $noteRootId, string $revisionId, int $revisionNumber): void
    {
        DB::table('notes')
            ->where('id', trim($noteRootId))
            ->update([
                'current_revision_id' => trim($revisionId),
                'latest_revision_number' => $revisionNumber,
            ]);
    }

    private function mapRevision(object $row): NoteRevision
    {
        $lines = DB::table('note_revision_lines')
            ->where('note_revision_id', (string) $row->id)
            ->orderBy('line_no')
            ->get()
            ->map(fn (object $line): NoteRevisionLineSnapshot => $this->mapLine($line))
            ->all();

        return NoteRevision::create(
            (string) $row->id,
            (string) $row->note_root_id,
            (int) $row->revision_number,
            isset($row->parent_revision_id) ? (string) $row->parent_revision_id : null,
            isset($row->created_by_actor_id) ? (string) $row->created_by_actor_id : null,
            isset($row->reason) ? (string) $row->reason : null,
            (string) $row->customer_name,
            isset($row->customer_phone) ? (string) $row->customer_phone : null,
            new DateTimeImmutable((string) $row->transaction_date),
            (int) $row->grand_total_rupiah,
            $lines,
            new DateTimeImmutable((string) $row->created_at),
        );
    }

    private function mapLine(object $row): NoteRevisionLineSnapshot
    {
        return NoteRevisionLineSnapshot::create(
            (string) $row->id,
            (string) $row->note_revision_id,
            isset($row->work_item_root_id) ? (string) $row->work_item_root_id : null,
            (int) $row->line_no,
            (string) $row->transaction_type,
            (string) $row->status,
            (int) $row->subtotal_rupiah,
            isset($row->service_label) ? (string) $row->service_label : null,
            isset($row->service_price_rupiah) ? (int) $row->service_price_rupiah : null,
            $this->decodePayload($row->payload ?? null),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encodePayload(array $payload): string
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? '{}' : $encoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(mixed $payload): array
    {
        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }
}
