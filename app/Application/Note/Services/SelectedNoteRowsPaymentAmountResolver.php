<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;

final class SelectedNoteRowsPaymentAmountResolver
{
    public function __construct(
        private readonly SelectedNoteBillingRowsProvider $billingRows,
        private readonly SelectedNoteRowsOutstandingTotalResolver $outstandingTotals,
    ) {
    }

    /** @param list<string> $selectedRowIds */
    public function resolve(string $noteId, array $selectedRowIds, int $requestedAmountRupiah): Result
    {
        $billingRowsResult = $this->billingRows->provide($noteId);
        if ($billingRowsResult->isFailure()) {
            return $billingRowsResult;
        }

        $billingRows = $billingRowsResult->data();
        if (! is_array($billingRows)) {
            return Result::failure('Nota tidak memiliki billing row pembayaran yang valid.', ['payment' => ['PAYMENT_INVALID_TARGET']]);
        }

        $totalResult = $this->outstandingTotals->resolve($billingRows, $selectedRowIds);
        if ($totalResult->isFailure()) {
            return $totalResult;
        }

        $selectedOutstandingTotal = (int) $totalResult->data();
        if ($selectedOutstandingTotal <= 0) {
            return Result::failure('Total outstanding billing row terpilih harus lebih besar dari 0.', ['payment' => ['INVALID_SELECTED_ROWS']]);
        }

        $effectiveAmountRupiah = $requestedAmountRupiah > 0
            ? $requestedAmountRupiah
            : $selectedOutstandingTotal;

        if ($effectiveAmountRupiah > $selectedOutstandingTotal) {
            return Result::failure(
                'Nominal pembayaran melebihi total outstanding billing row yang dipilih.',
                ['payment' => ['INVALID_PAYMENT_AMOUNT']]
            );
        }

        return Result::success([
            'amount_rupiah' => $effectiveAmountRupiah,
            'selected_outstanding_total_rupiah' => $selectedOutstandingTotal,
        ]);
    }
}
