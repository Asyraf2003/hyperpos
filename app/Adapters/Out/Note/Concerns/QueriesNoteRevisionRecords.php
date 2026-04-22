<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Concerns;

use App\Core\Note\Revision\NoteRevision;
use Illuminate\Support\Facades\DB;

trait QueriesNoteRevisionRecords
{
    public function findById(string $revisionId): ?NoteRevision
    {
        $normalized = trim($revisionId);

        if ($normalized === '') {
            return null;
        }

        $row = DB::table('note_revisions')->where('id', $normalized)->first();

        return $row !== null ? $this->rows->map($row) : null;
    }

    public function findCurrentByRootId(string $noteRootId): ?NoteRevision
    {
        $normalized = trim($noteRootId);

        if ($normalized === '') {
            return null;
        }

        $currentRevisionId = DB::table('notes')->where('id', $normalized)->value('current_revision_id');

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

        return DB::table('note_revisions')
            ->where('note_root_id', $normalized)
            ->orderByDesc('revision_number')
            ->limit(max($limit, 1))
            ->get()
            ->map(fn (object $row) => $this->rows->map($row))
            ->all();
    }

    public function existsForRootId(string $noteRootId): bool
    {
        $normalized = trim($noteRootId);

        return $normalized !== ''
            && DB::table('note_revisions')->where('note_root_id', $normalized)->exists();
    }
}
