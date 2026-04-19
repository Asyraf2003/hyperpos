<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Ports\Out\Note\TransactionWorkspaceDraftWriterPort;
use Illuminate\Support\Facades\DB;
use JsonException;

final class DatabaseTransactionWorkspaceDraftWriterAdapter implements TransactionWorkspaceDraftWriterPort
{
    public function save(
        string $draftId,
        string $actorId,
        string $workspaceMode,
        string $workspaceKey,
        ?string $noteId,
        array $payload
    ): void {
        $now = now()->format('Y-m-d H:i:s');

        $exists = DB::table('transaction_workspace_drafts')
            ->where('actor_id', trim($actorId))
            ->where('workspace_key', trim($workspaceKey))
            ->exists();

        if ($exists) {
            DB::table('transaction_workspace_drafts')
                ->where('actor_id', trim($actorId))
                ->where('workspace_key', trim($workspaceKey))
                ->update([
                    'workspace_mode' => trim($workspaceMode),
                    'note_id' => $noteId !== null ? trim($noteId) : null,
                    'payload_json' => $this->encode($payload),
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('transaction_workspace_drafts')->insert([
            'id' => trim($draftId),
            'actor_id' => trim($actorId),
            'workspace_mode' => trim($workspaceMode),
            'workspace_key' => trim($workspaceKey),
            'note_id' => $noteId !== null ? trim($noteId) : null,
            'payload_json' => $this->encode($payload),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encode(array $payload): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (JsonException) {
            return '{}';
        }
    }
}
