<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note\Support;

use App\Adapters\In\Http\Requests\Note\RecordClosedNoteRefundRequest;
use Illuminate\Http\RedirectResponse;

final class ClosedNoteRefundResponseFactory
{
    public function failed(?string $message, string $fallback = 'Refund gagal dicatat.'): RedirectResponse
    {
        return back()
            ->withErrors(['refund' => $message ?? $fallback])
            ->withInput();
    }

    public function success(
        RecordClosedNoteRefundRequest $request,
        NoteRouteAreaResolver $routes,
        ?string $message,
    ): RedirectResponse {
        return redirect()
            ->route($routes->indexRoute($request))
            ->with('success', $message ?? 'Refund berhasil dicatat.');
    }
}
