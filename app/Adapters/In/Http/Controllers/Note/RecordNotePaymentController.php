<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Requests\Note\RecordNotePaymentRequest;
use App\Application\Note\Services\SelectedNoteRowsPaymentAmountResolver;
use App\Application\Payment\UseCases\RecordAndAllocateNotePaymentHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class RecordNotePaymentController extends Controller
{
    public function __invoke(
        string $noteId,
        RecordNotePaymentRequest $request,
        SelectedNoteRowsPaymentAmountResolver $selectedRowsResolver,
        RecordAndAllocateNotePaymentHandler $flow,
    ): RedirectResponse {
        $data = $request->validated();

        $selectedRowIds = is_array($data['selected_row_ids'] ?? null)
            ? array_values($data['selected_row_ids'])
            : [];

        $amountResult = $selectedRowsResolver->resolve(
            $noteId,
            $selectedRowIds,
            (int) ($data['amount_paid'] ?? 0),
        );

        if ($amountResult->isFailure()) {
            return back()->withErrors(['payment' => $amountResult->message() ?? 'Pembayaran gagal.'])->withInput();
        }

        $amount = (int) ($amountResult->data()['amount_rupiah'] ?? 0);

        if (($data['payment_method'] ?? '') === 'cash' && (int) ($data['amount_received'] ?? 0) < $amount) {
            return back()->withErrors(['payment' => 'Uang masuk cash tidak boleh kurang dari total yang dibayar.'])->withInput();
        }

        $result = $flow->handle(
            $noteId,
            $amount,
            (string) $data['paid_at'],
            $selectedRowIds,
        );

        if ($result->isFailure()) {
            return back()->withErrors(['payment' => $result->message() ?? 'Pembayaran gagal dicatat.'])->withInput();
        }

        return redirect()
            ->route('cashier.notes.show', ['noteId' => $noteId])
            ->with('success', $this->successMessage($data, $amount));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function successMessage(array $data, int $amount): string
    {
        if (($data['payment_method'] ?? '') !== 'cash') {
            return 'Pembayaran berhasil dicatat.';
        }

        $change = max(((int) ($data['amount_received'] ?? 0)) - $amount, 0);

        return $change > 0
            ? 'Pembayaran berhasil dicatat. Kembalian: ' . number_format($change, 0, ',', '.')
            : 'Pembayaran berhasil dicatat.';
    }
}
