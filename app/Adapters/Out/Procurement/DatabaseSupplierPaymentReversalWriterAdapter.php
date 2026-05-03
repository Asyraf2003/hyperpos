<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierPaymentReversalWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierPaymentReversalWriterAdapter implements SupplierPaymentReversalWriterPort
{
    public function findPaymentSnapshotForReversal(string $paymentId): ?array
    {
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
            return null;
        }

        return [
            'payment_id' => (string) $payment->id,
            'supplier_invoice_id' => (string) $payment->supplier_invoice_id,
            'amount_rupiah' => (int) $payment->amount_rupiah,
            'paid_at' => (string) $payment->paid_at,
            'proof_status' => (string) $payment->proof_status,
        ];
    }

    public function paymentAlreadyReversed(string $paymentId): bool
    {
        return DB::table('supplier_payment_reversals')
            ->where('supplier_payment_id', $paymentId)
            ->exists();
    }

    public function record(array $record): void
    {
        $now = Carbon::now();

        DB::table('supplier_payment_reversals')->insert(array_merge($record, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
