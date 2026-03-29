<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

final class NoteOutstandingPaymentAmountResolver
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly CustomerRefundReaderPort $refunds,
    ) {
    }

    public function resolveFull(string $noteId): Result
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return Result::failure('Nota tidak ditemukan.', ['payment' => ['PAYMENT_INVALID_TARGET']]);
        }

        $grandTotal = $note->totalRupiah()->amount();
        $allocated = $this->allocations->getTotalAllocatedAmountByNoteId($note->id())->amount();
        $refunded = $this->refunds->getTotalRefundedAmountByNoteId($note->id())->amount();
        $netPaid = max($allocated - $refunded, 0);
        $outstanding = max($grandTotal - $netPaid, 0);

        if ($outstanding <= 0) {
            return Result::failure('Nota sudah lunas.', ['payment' => ['PAYMENT_ALREADY_PAID']]);
        }

        return Result::success([
            'amount_rupiah' => $outstanding,
            'grand_total_rupiah' => $grandTotal,
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => $outstanding,
        ]);
    }

    public function resolvePartial(string $noteId, int $amountRupiah): Result
    {
        $full = $this->resolveFull($noteId);

        if ($full->isFailure()) {
            return $full;
        }

        if ($amountRupiah <= 0) {
            return Result::failure('Nominal pembayaran sebagian harus lebih dari 0.', ['payment' => ['INVALID_PARTIAL_AMOUNT']]);
        }

        $outstanding = (int) ($full->data()['outstanding_rupiah'] ?? 0);

        if ($amountRupiah >= $outstanding) {
            return Result::failure('Nominal pembayaran sebagian harus lebih kecil dari sisa tagihan.', ['payment' => ['INVALID_PARTIAL_AMOUNT']]);
        }

        return Result::success([
            'amount_rupiah' => $amountRupiah,
            'grand_total_rupiah' => (int) ($full->data()['grand_total_rupiah'] ?? 0),
            'net_paid_rupiah' => (int) ($full->data()['net_paid_rupiah'] ?? 0),
            'outstanding_rupiah' => $outstanding,
        ]);
    }
}
