<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Note;

use App\Adapters\In\Http\Requests\Note\ReopenClosedNoteRequest;
use App\Application\Note\UseCases\ReopenClosedNoteHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class ReopenClosedNoteController extends Controller
{
    public function __invoke(
        string $noteId,
        ReopenClosedNoteRequest $request,
        ReopenClosedNoteHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();
        $actorId = (string) $request->user()->getAuthIdentifier();

        $result = $useCase->handle(
            $noteId,
            (string) $data['reason'],
            $actorId,
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors(['reopen' => $result->message() ?? 'Note gagal dibuka kembali.'])
                ->withInput();
        }

        return redirect()
            ->route('admin.notes.show', ['noteId' => $noteId])
            ->with('success', $result->message() ?? 'Note berhasil dibuka kembali.');
    }
}
