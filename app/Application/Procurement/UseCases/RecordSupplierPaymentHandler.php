<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierPaymentPreflight;
use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierPayment\SupplierPayment;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierPaymentWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use Throwable;

final class RecordSupplierPaymentHandler
{
    public function __construct(
        private readonly SupplierPaymentWriterPort $writer,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
        private readonly SupplierPaymentPreflight $preflight,
    ) {
    }

    public function handle(string $supplierInvoiceId, int $amountRupiah, string $paidAt, string $performedByActorId): Result
    {
        $prepared = $this->preflight->prepare($supplierInvoiceId, $amountRupiah, $paidAt, $performedByActorId);

        if ($prepared->isFailure()) {
            return $prepared;
        }

        $started = false;

        try {
            $data = $prepared->data();
            $invoice = $data['invoice'];
            $actorId = (string) $data['actor_id'];
            $date = $data['paid_at'];
            $outstanding = (int) $data['outstanding_rupiah'];

            $this->transactions->begin();
            $started = true;

            $paymentId = $this->uuid->generate();

            $this->writer->create(
                SupplierPayment::create(
                    $paymentId,
                    $invoice->id(),
                    \App\Core\Shared\ValueObjects\Money::fromInt((int) $data['amount_rupiah']),
                    $date,
                    SupplierPayment::PROOF_STATUS_PENDING,
                    null
                )
            );

            $this->audit->record('supplier_payment_recorded', [
                'supplier_payment_id' => $paymentId,
                'supplier_invoice_id' => $invoice->id(),
                'amount_rupiah' => (int) $data['amount_rupiah'],
                'outstanding_before_rupiah' => $outstanding,
                'outstanding_after_rupiah' => $outstanding - (int) $data['amount_rupiah'],
                'paid_at' => $date->format('Y-m-d'),
                'performed_by_actor_id' => $actorId,
                'proof_status' => SupplierPayment::PROOF_STATUS_PENDING,
            ]);

            $this->transactions->commit();

            return Result::success([
                'supplier_payment_id' => $paymentId,
                'supplier_invoice_id' => $invoice->id(),
                'amount_rupiah' => (int) $data['amount_rupiah'],
                'outstanding_rupiah' => $outstanding - (int) $data['amount_rupiah'],
            ], 'Pembayaran supplier berhasil dicatat.');
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
