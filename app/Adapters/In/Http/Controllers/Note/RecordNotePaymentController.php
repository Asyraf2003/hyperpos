<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Controllers\Note\Support\NotePaymentRedirectMessageBuilder;
use App\Adapters\In\Http\Controllers\Note\Support\NoteRouteAreaResolver;
use App\Adapters\In\Http\Requests\Note\RecordNotePaymentRequest;
use App\Application\Note\Services\SelectedNoteRowsPaymentAmountResolver;
use App\Application\Payment\Services\RecordNotePaymentIdempotencyService;
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
        RecordNotePaymentIdempotencyService $idempotency,
        NoteRouteAreaResolver $routes,
        NotePaymentRedirectMessageBuilder $messages,
    ): RedirectResponse {
        $data = $request->validated();

        $user = $request->user();
        $idempotencyPayload = $data + [
            '_actor_id' => $user !== null ? (string) $user->getAuthIdentifier() : '',
            '_note_id' => trim($noteId),
        ];

        $replayed = $idempotency->replay($idempotencyPayload);

        if ($replayed !== null) {
            if ($replayed->isFailure()) {
                return back()->withErrors(['payment' => $replayed->message() ?? 'Pembayaran gagal dicatat.'])->withInput();
            }

            return redirect()
                ->route($routes->showRoute($request), ['noteId' => $noteId])
                ->with('success', $replayed->message() ?? 'Pembayaran berhasil dicatat.');
        }

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
        $paymentMethod = (string) ($data['payment_method'] ?? 'unknown');
        $amountReceived = $paymentMethod === 'cash'
            ? (int) ($data['amount_received'] ?? 0)
            : null;

        if ($paymentMethod === 'cash' && (int) ($data['amount_received'] ?? 0) < $amount) {
            return back()->withErrors(['payment' => 'Uang masuk cash tidak boleh kurang dari total yang dibayar.'])->withInput();
        }

        $result = $flow->handle(
            $noteId,
            $amount,
            (string) $data['paid_at'],
            $selectedRowIds,
            $paymentMethod,
            $amountReceived,
            $idempotencyPayload,
        );

        if ($result->isFailure()) {
            return back()->withErrors(['payment' => $result->message() ?? 'Pembayaran gagal dicatat.'])->withInput();
        }

        return redirect()
            ->route($routes->showRoute($request), ['noteId' => $noteId])
            ->with('success', $messages->success($data, $amount));
    }
}
