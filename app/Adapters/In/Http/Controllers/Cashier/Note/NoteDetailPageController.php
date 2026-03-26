<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class NoteDetailPageController
{
    public function __invoke(Request $request, string $noteId): View
    {
        return view('cashier.notes.show', [
            'pageTitle' => 'Detail Nota',
            'noteId' => $noteId,
        ]);
    }
}
