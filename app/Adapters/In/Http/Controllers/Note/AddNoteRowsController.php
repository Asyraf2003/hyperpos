<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Requests\Note\AddNoteRowsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class AddNoteRowsController extends Controller
{
    public function __invoke(
        string $noteId,
        AddNoteRowsRequest $request,
        CreateNoteRowsAction $action,
    ): RedirectResponse {
        $result = $action->handle($noteId, $request->validated()['rows'] ?? []);

        if ($result !== null && $result->isFailure()) {
            return back()->withErrors(['note' => $result->message() ?? 'Baris nota gagal ditambahkan.'])->withInput();
        }

        return redirect()
            ->route('cashier.notes.show', ['noteId' => $noteId])
            ->with('success', 'Baris nota berhasil ditambahkan.');
    }
}
