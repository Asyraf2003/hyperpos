<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Adapters\In\Http\Requests\Note\SaveTransactionWorkspaceDraftRequest;
use App\Application\Note\Services\SaveTransactionWorkspaceDraftOperation;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class SaveTransactionWorkspaceDraftController extends Controller
{
    public function __invoke(
        SaveTransactionWorkspaceDraftRequest $request,
        SaveTransactionWorkspaceDraftOperation $operation,
    ): JsonResponse {
        $workspaceKey = $request->workspaceKey();

        $operation->handle(
            (string) $request->user()->getAuthIdentifier(),
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
