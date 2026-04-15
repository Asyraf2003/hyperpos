<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Requests\Note\RecordClosedNoteRefundRequest;
use App\Application\Note\Services\NoteOperationalStatusResolver;
use App\Application\Payment\UseCases\RecordCustomerRefundHandler;
use App\Ports\Out\Note\NoteReaderPort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class RecordClosedNoteRefundController extends Controller
{
    public function __invoke(
        string $noteId,
        RecordClosedNoteRefundRequest $request,
        NoteReaderPort $notes,
        NoteOperationalStatusResolver $statuses,
        RecordCustomerRefundHandler $handler,
    ): RedirectResponse {
        $note = $notes->getById(trim($noteId));

        if ($note === null) {
            abort(404);
        }

        if (!$statuses->isClose($note)) {
            return back()
                ->withErrors(['refund' => 'Refund hanya boleh dilakukan pada nota close.'])
                ->withInput();
        }

        $data = $request->validated();
        $actorId = (string) $request->user()->getAuthIdentifier();

        $result = $handler->handle(
            (string) $data['customer_payment_id'],
            $note->id(),
            (int) $data['amount_rupiah'],
            (string) $data['refunded_at'],
            (string) $data['reason'],
            $actorId,
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors(['refund' => $result->message() ?? 'Refund gagal dicatat.'])
                ->withInput();
        }

        return redirect()
            ->route('cashier.notes.index')
            ->with('success', $result->message() ?? 'Refund berhasil dicatat.');
    }
}
