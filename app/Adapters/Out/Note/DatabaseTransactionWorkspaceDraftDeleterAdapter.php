<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Ports\Out\Note\TransactionWorkspaceDraftDeleterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseTransactionWorkspaceDraftDeleterAdapter implements TransactionWorkspaceDraftDeleterPort
{
    public function deleteByActorAndWorkspaceKey(string $actorId, string $workspaceKey): void
    {
        DB::table('transaction_workspace_drafts')
            ->where('actor_id', trim($actorId))
            ->where('workspace_key', trim($workspaceKey))
            ->delete();
    }
}
