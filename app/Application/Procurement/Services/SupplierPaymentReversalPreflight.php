<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Procurement\SupplierPaymentReversalWriterPort;

final class SupplierPaymentReversalPreflight
{
    public function __construct(
        private readonly SupplierPaymentReversalWriterPort $reversals,
    ) {
    }

    public function prepare(string $supplierPaymentId, string $reason, string $performedByActorId): Result
    {
        $paymentId = trim($supplierPaymentId);
        $reason = trim($reason);
        $actorId = trim($performedByActorId);

        if ($reason === '') {
            return Result::failure(
                'Alasan reversal pembayaran supplier wajib diisi.',
                ['supplier_payment_reversal' => ['SUPPLIER_PAYMENT_REVERSAL_REASON_REQUIRED']]
            );
        }

        if ($actorId === '') {
            return Result::failure(
                'Actor reversal pembayaran supplier wajib ada.',
                ['supplier_payment_reversal' => ['INVALID_SUPPLIER_PAYMENT_REVERSAL']]
            );
        }

        $payment = $this->reversals->findPaymentSnapshotForReversal($paymentId);

        if ($payment === null) {
            return Result::failure(
                'Pembayaran supplier tidak ditemukan.',
                ['supplier_payment_reversal' => ['SUPPLIER_PAYMENT_NOT_FOUND']]
            );
        }

        if ($this->reversals->paymentAlreadyReversed($paymentId)) {
            return Result::failure(
                'Pembayaran supplier ini sudah direverse.',
                ['supplier_payment_reversal' => ['SUPPLIER_PAYMENT_ALREADY_REVERSED']]
            );
        }

        return Result::success([
            'payment_id' => $payment['payment_id'],
            'supplier_invoice_id' => $payment['supplier_invoice_id'],
            'amount_rupiah' => $payment['amount_rupiah'],
            'paid_at' => $payment['paid_at'],
            'proof_status' => $payment['proof_status'],
            'reason' => $reason,
            'actor_id' => $actorId,
        ]);
    }
}
