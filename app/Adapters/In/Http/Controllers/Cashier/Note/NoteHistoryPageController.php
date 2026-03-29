<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class NoteHistoryPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $today = date('Y-m-d');

        $filters = [
            'date' => $this->resolveString($request, 'date') ?? $today,
            'search' => $this->resolveString($request, 'search') ?? '',
            'payment_status' => $this->resolveString($request, 'payment_status') ?? '',
            'work_status' => $this->resolveString($request, 'work_status') ?? '',
        ];

        return view('cashier.notes.index', [
            'pageTitle' => 'Riwayat Nota',
            'filters' => $filters,
        ]);
    }

    private function resolveString(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
