<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Requests\Note\StoreTransactionWorkspaceRequest;
use App\Application\Note\UseCases\CreateTransactionWorkspaceHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class StoreTransactionWorkspaceController extends Controller
{
    public function __invoke(
        StoreTransactionWorkspaceRequest $request,
        CreateTransactionWorkspaceHandler $handler,
    ): RedirectResponse {
        $result = $handler->handle($request->validated());

        if ($result->isFailure()) {
            return back()->withErrors([
                'workspace' => $result->message() ?? 'Workspace nota gagal disimpan.',
            ])->withInput();
        }

        $noteId = $this->extractNoteId($result->data());

        if ($noteId === null) {
            return back()->withErrors([
                'workspace' => 'ID nota tidak ditemukan setelah penyimpanan workspace.',
            ])->withInput();
        }

        return redirect()
            ->route('cashier.notes.show', ['noteId' => $noteId])
            ->with('success', $result->message() ?? 'Workspace nota berhasil disimpan.');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractNoteId(array $payload): ?string
    {
        $note = $payload['note'] ?? null;

        if (! is_array($note)) {
            return null;
        }

        $noteId = $note['id'] ?? null;

        if (! is_string($noteId) || trim($noteId) === '') {
            return null;
        }

        return trim($noteId);
    }
}
