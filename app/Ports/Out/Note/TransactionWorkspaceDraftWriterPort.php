<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

interface TransactionWorkspaceDraftWriterPort
{
    /**
     * @param array<string, mixed> $payload
     */
    public function save(
        string $draftId,
        string $actorId,
        string $workspaceMode,
        string $workspaceKey,
        ?string $noteId,
        array $payload
    ): void;
}
