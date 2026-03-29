<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Note;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class NoteHistoryPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.notes.index', [
            'pageTitle' => 'Riwayat Nota',
        ]);
    }
}
