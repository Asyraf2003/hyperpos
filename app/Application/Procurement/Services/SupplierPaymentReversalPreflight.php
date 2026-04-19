<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use Illuminate\Support\Facades\DB;

final class SupplierPaymentReversalPreflight
{
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

        $payment = DB::table('supplier_payments')
            ->where('id', $paymentId)
            ->first([
                'id',
                'supplier_invoice_id',
                'amount_rupiah',
                'paid_at',
                'proof_status',
            ]);

        if ($payment === null) {
            return Result::failure(
                'Pembayaran supplier tidak ditemukan.',
                ['supplier_payment_reversal' => ['SUPPLIER_PAYMENT_NOT_FOUND']]
            );
        }

        $alreadyReversed = DB::table('supplier_payment_reversals')
            ->where('supplier_payment_id', $paymentId)
            ->exists();

        if ($alreadyReversed) {
            return Result::failure(
                'Pembayaran supplier ini sudah direverse.',
                ['supplier_payment_reversal' => ['SUPPLIER_PAYMENT_ALREADY_REVERSED']]
            );
        }

        return Result::success([
            'payment_id' => (string) $payment->id,
            'supplier_invoice_id' => (string) $payment->supplier_invoice_id,
            'amount_rupiah' => (int) $payment->amount_rupiah,
            'paid_at' => (string) $payment->paid_at,
            'proof_status' => (string) $payment->proof_status,
            'reason' => $reason,
            'actor_id' => $actorId,
        ]);
    }
}
