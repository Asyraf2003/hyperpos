<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Adapters\In\Http\Requests\Note\SaveTransactionWorkspaceDraftRequest;
use App\Ports\Out\Note\TransactionWorkspaceDraftReaderPort;
use App\Ports\Out\Note\TransactionWorkspaceDraftWriterPort;
use App\Ports\Out\UuidPort;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class SaveTransactionWorkspaceDraftController extends Controller
{
    public function __invoke(
        SaveTransactionWorkspaceDraftRequest $request,
        TransactionWorkspaceDraftReaderPort $drafts,
        TransactionWorkspaceDraftWriterPort $writer,
        UuidPort $uuid,
    ): JsonResponse {
        $actorId = (string) $request->user()->getAuthIdentifier();
        $workspaceKey = $request->workspaceKey();
        $existing = $drafts->findByActorAndWorkspaceKey($actorId, $workspaceKey);

        $writer->save(
            $existing['id'] ?? $uuid->generate(),
            $actorId,
            (string) $request->input('workspace_mode'),
            $workspaceKey,
            $request->input('note_id') !== null ? (string) $request->input('note_id') : null,
            $request->draftPayload(),
        );

        return response()->json([
            'success' => true,
            'data' => [
                'workspace_key' => $workspaceKey,
                'saved' => true,
            ],
        ]);
    }
}
