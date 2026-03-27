<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Services\NoteDetailPageDataBuilder;
use Illuminate\Contracts\View\View;

final class NoteDetailPageController
{
    public function __invoke(string $noteId, NoteDetailPageDataBuilder $builder): View
    {
        $data = $builder->build($noteId);

        abort_if($data === null, 404);

        return view('cashier.notes.show', $data);
    }
}
