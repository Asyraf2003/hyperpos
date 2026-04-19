<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Requests\Note\CorrectPaidWorkItemStatusRequest;
use App\Application\Note\UseCases\CorrectPaidWorkItemStatusHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CorrectPaidWorkItemStatusController extends Controller
{
    public function __invoke(
        string $noteId,
        CorrectPaidWorkItemStatusRequest $request,
        CorrectPaidWorkItemStatusHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();
        $actorId = (string) $request->user()->getAuthIdentifier();

        $result = $useCase->handle(
            $noteId,
            (int) $data['line_no'],
            (string) $data['target_status'],
            (string) $data['reason'],
            $actorId,
        );

        if ($result->isFailure()) {
            return back()->withErrors(['correction' => $result->message() ?? 'Correction status gagal disimpan.'])->withInput();
        }

        return redirect()
            ->route('cashier.notes.show', ['noteId' => $noteId])
            ->with('success', 'Correction status work item berhasil disimpan.');
    }
}
