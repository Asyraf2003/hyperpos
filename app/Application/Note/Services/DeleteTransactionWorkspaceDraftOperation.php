<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\TransactionWorkspaceDraftDeleterPort;

final readonly class DeleteTransactionWorkspaceDraftOperation
{
    public function __construct(private TransactionWorkspaceDraftDeleterPort $drafts)
    {
    }

    public function deleteForActorAndWorkspace(string $actorId, string $workspaceKey): void
    {
        $this->drafts->deleteByActorAndWorkspaceKey($actorId, $workspaceKey);
    }
}
