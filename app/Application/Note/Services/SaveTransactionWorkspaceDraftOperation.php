<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\TransactionWorkspaceDraftReaderPort;
use App\Ports\Out\Note\TransactionWorkspaceDraftWriterPort;
use App\Ports\Out\UuidPort;

final readonly class SaveTransactionWorkspaceDraftOperation
{
    public function __construct(
        private TransactionWorkspaceDraftReaderPort $drafts,
        private TransactionWorkspaceDraftWriterPort $writer,
        private UuidPort $uuid,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handle(
        string $actorId,
        string $workspaceMode,
        string $workspaceKey,
        ?string $noteId,
        array $payload,
    ): void {
        $existing = $this->drafts->findByActorAndWorkspaceKey($actorId, $workspaceKey);

        $this->writer->save(
            $existing['id'] ?? $this->uuid->generate(),
            $actorId,
            $workspaceMode,
            $workspaceKey,
            $noteId,
            $payload,
        );
    }
}
