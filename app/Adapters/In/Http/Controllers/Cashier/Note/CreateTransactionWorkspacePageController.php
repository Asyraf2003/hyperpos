<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Services\TransactionWorkspaceDraftData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CreateTransactionWorkspacePageController extends Controller
{
    public function __invoke(
        Request $request,
        TransactionWorkspaceDraftData $draftData,
    ): View {
        $actorId = (string) $request->user()->getAuthIdentifier();
        $workspaceKey = 'create';

        return view('cashier.notes.workspace', [
            'workspaceMode' => 'create',
            'noteId' => null,
            'workspaceKey' => $workspaceKey,
            'draft' => $draftData->findByActorAndWorkspaceKey($actorId, $workspaceKey),
        ]);
    }
}
