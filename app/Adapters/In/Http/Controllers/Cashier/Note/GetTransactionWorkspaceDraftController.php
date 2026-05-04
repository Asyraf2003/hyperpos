<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Services\TransactionWorkspaceDraftData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetTransactionWorkspaceDraftController extends Controller
{
    public function __invoke(
        Request $request,
        TransactionWorkspaceDraftData $draftData,
    ): JsonResponse {
        $actorId = (string) $request->user()->getAuthIdentifier();
        $workspaceMode = trim((string) $request->query('workspace_mode', 'create'));
        $noteId = trim((string) $request->query('note_id', ''));

        if (! in_array($workspaceMode, ['create', 'edit'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Workspace mode draft tidak valid.',
            ], 422);
        }

        $workspaceKey = $workspaceMode === 'edit'
            ? 'edit:' . $noteId
            : 'create';

        $draft = $draftData->findByActorAndWorkspaceKey($actorId, $workspaceKey);

        return response()->json([
            'success' => true,
            'data' => [
                'workspace_key' => $workspaceKey,
                'draft' => $draft,
            ],
        ]);
    }
}
