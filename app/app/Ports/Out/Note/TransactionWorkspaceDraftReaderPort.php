<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

interface TransactionWorkspaceDraftReaderPort
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByActorAndWorkspaceKey(string $actorId, string $workspaceKey): ?array;
}
