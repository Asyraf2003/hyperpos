<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ReverseSupplierPaymentHandler
{
    public function __construct(
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
        private readonly UuidPort $uuid,
    ) {
    }

    public function handle(string $supplierPaymentId, string $reason, string $performedByActorId): Result
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
            throw new DomainException('Actor reversal pembayaran supplier wajib ada.');
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

        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $reversalId = $this->uuid->generate();

            DB::table('supplier_payment_reversals')->insert([
                'id' => $reversalId,
                'supplier_payment_id' => $paymentId,
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit->record('supplier_payment_reversed', [
                'reversal_id' => $reversalId,
                'supplier_payment_id' => $paymentId,
                'supplier_invoice_id' => (string) $payment->supplier_invoice_id,
                'amount_rupiah' => (int) $payment->amount_rupiah,
                'paid_at' => (string) $payment->paid_at,
                'proof_status' => (string) $payment->proof_status,
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
            ]);

            $this->transactions->commit();

            return Result::success(
                ['id' => $reversalId],
                'Reversal pembayaran supplier berhasil dicatat.'
            );
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
