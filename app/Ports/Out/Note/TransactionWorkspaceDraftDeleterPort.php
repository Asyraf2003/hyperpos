<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

interface TransactionWorkspaceDraftDeleterPort
{
    public function deleteByActorAndWorkspaceKey(string $actorId, string $workspaceKey): void;
}
