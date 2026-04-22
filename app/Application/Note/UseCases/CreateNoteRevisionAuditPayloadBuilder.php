<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Core\Note\Revision\NoteRevision;

final class CreateNoteRevisionAuditPayloadBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(
        string $noteRootId,
        string $parentRevisionId,
        ?string $actorId,
        string $reason,
        NoteRevision $revision,
    ): array {
        return [
            'note_root_id' => $noteRootId,
            'revision_id' => $revision->id(),
            'revision_number' => $revision->revisionNumber(),
            'parent_revision_id' => $parentRevisionId,
            'reason' => $reason,
            'actor_id' => $actorId,
            'line_count' => $revision->lineCount(),
            'grand_total_rupiah' => $revision->grandTotalRupiah(),
        ];
    }
}
