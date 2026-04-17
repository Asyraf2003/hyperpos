<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Requests\Note\RecordClosedNoteRefundRequest;
use App\Application\Note\Services\SelectedNoteRowsRefundAmountResolver;
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
        SelectedNoteRowsRefundAmountResolver $selectedRowsResolver,
        RecordCustomerRefundHandler $handler,
    ): RedirectResponse {
        $note = $notes->getById(trim($noteId));

        if ($note === null) {
            abort(404);
        }

        $data = $request->validated();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $selectedRowIds = is_array($data['selected_row_ids'] ?? null)
            ? array_values($data['selected_row_ids'])
            : [];

        $amountResult = $selectedRowsResolver->resolve(
            $note->id(),
            $selectedRowIds,
            (int) ($data['amount_rupiah'] ?? 0),
        );

        if ($amountResult->isFailure()) {
            return back()
                ->withErrors(['refund' => $amountResult->message() ?? 'Refund gagal dicatat.'])
                ->withInput();
        }

        $result = $handler->handle(
            (string) $data['customer_payment_id'],
            $note->id(),
            (int) ($amountResult->data()['amount_rupiah'] ?? 0),
            (string) $data['refunded_at'],
            (string) $data['reason'],
            $actorId,
            $selectedRowIds,
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
