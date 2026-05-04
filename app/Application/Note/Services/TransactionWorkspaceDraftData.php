<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\TransactionWorkspaceDraftReaderPort;

final class TransactionWorkspaceDraftData
{
    public function __construct(
        private readonly TransactionWorkspaceDraftReaderPort $drafts,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByActorAndWorkspaceKey(string $actorId, string $workspaceKey): ?array
    {
        return $this->drafts->findByActorAndWorkspaceKey($actorId, $workspaceKey);
    }
}
