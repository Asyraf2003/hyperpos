<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Controllers\Note\Support\NoteRouteAreaResolver;
use App\Adapters\In\Http\Requests\Note\UpdateTransactionWorkspaceRequest;
use App\Application\Note\Services\DeleteTransactionWorkspaceDraftOperation;
use App\Application\Note\UseCases\UpdateTransactionWorkspaceHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class UpdateTransactionWorkspaceController extends Controller
{
    public function __invoke(
        string $noteId,
        UpdateTransactionWorkspaceRequest $request,
        UpdateTransactionWorkspaceHandler $handler,
        DeleteTransactionWorkspaceDraftOperation $drafts,
        NoteRouteAreaResolver $routes,
    ): RedirectResponse {
        $result = $handler->handle($noteId, $request->validated());

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'workspace' => $result->message() ?? 'Perubahan workspace nota gagal disimpan.',
                ])
                ->withInput();
        }

        $actorId = $request->user()?->getAuthIdentifier();

        if ($actorId !== null) {
            $drafts->deleteForActorAndWorkspace((string) $actorId, 'edit:' . trim($noteId));
        }

        return redirect()
            ->route($routes->showRoute($request), ['noteId' => $noteId])
            ->with('success', $result->message() ?? 'Perubahan workspace nota berhasil disimpan.');
    }
}
