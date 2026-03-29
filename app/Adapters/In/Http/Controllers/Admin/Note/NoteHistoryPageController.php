<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Note;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class NoteHistoryPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $today = date('Y-m-d');

        $filters = [
            'date_from' => $this->resolveString($request, 'date_from') ?? $today,
            'date_to' => $this->resolveString($request, 'date_to') ?? $today,
            'search' => $this->resolveString($request, 'search') ?? '',
            'payment_status' => $this->resolveString($request, 'payment_status') ?? '',
            'editability' => $this->resolveString($request, 'editability') ?? '',
            'work_summary' => $this->resolveString($request, 'work_summary') ?? '',
        ];

        return view('admin.notes.index', [
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
