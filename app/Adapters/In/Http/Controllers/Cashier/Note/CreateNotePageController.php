<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Services\CreateNotePageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreateNotePageController extends Controller
{
    public function __invoke(CreateNotePageDataBuilder $builder): View
    {
        return view('cashier.notes.create', [
            'pageTitle' => 'Buat Nota',
            'formAction' => route('notes.create'),
            'transactionDateDefault' => date('Y-m-d'),
            ...$builder->build(),
        ]);
    }
}
