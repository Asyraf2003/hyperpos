<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceListProjectionService;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Procurement\SupplierInvoiceVoidWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class VoidSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierInvoiceListProjectionService $projection,
        private readonly SupplierInvoiceVoidWriterPort $voids,
        private readonly TransactionManagerPort $transactions,
    ) {
    }

    public function handle(
        string $supplierInvoiceId,
        string $voidReason,
        ?string $performedByActorId = null,
    ): Result {
        $normalizedInvoiceId = trim($supplierInvoiceId);
        $reason = trim($voidReason);

        $this->transactions->begin();

        try {
            $invoice = $this->voids->findVoidSnapshotForUpdate($normalizedInvoiceId);

            if ($invoice === null) {
                return $this->fail(
                    'Nota supplier tidak ditemukan.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]
                );
            }

            if ($invoice['voided_at'] !== null) {
                return $this->fail(
                    'Nota supplier ini sudah dibatalkan.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_ALREADY_VOIDED']]
                );
            }

            if ($this->voids->receiptExists($normalizedInvoiceId)) {
                return $this->fail(
                    'Nota supplier tidak bisa dibatalkan karena receipt sudah tercatat.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_VOID_RECEIPT_EXISTS']]
                );
            }

            if ($this->voids->paymentExists($normalizedInvoiceId)) {
                return $this->fail(
                    'Nota supplier tidak bisa dibatalkan karena pembayaran sudah tercatat.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_VOID_PAYMENT_EXISTS']]
                );
            }

            $this->voids->voidInvoice($normalizedInvoiceId, $reason);
            $this->voids->recordVoidAuditIfAvailable($normalizedInvoiceId, $reason, $performedByActorId);
            $this->projection->syncInvoice($normalizedInvoiceId);
            $this->transactions->commit();

            return Result::success(
                ['supplier_invoice_id' => $normalizedInvoiceId],
                'Nota supplier berhasil dibatalkan.'
            );
        } catch (Throwable $e) {
            $this->transactions->rollBack();

            throw $e;
        }
    }

    private function fail(string $message, array $errors): Result
    {
        $this->transactions->rollBack();

        return Result::failure($message, $errors);
    }
}
