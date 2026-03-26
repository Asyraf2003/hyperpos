<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class CreateNotePageController
{
    public function __invoke(Request $request): View
    {
        return view('cashier.notes.create', [
            'pageTitle' => 'Buat Nota',
            'today' => now()->toDateString(),
            'availableLineTypes' => [
                ['value' => 'product', 'label' => 'Produk'],
                ['value' => 'service', 'label' => 'Servis'],
            ],
        ]);
    }
}
