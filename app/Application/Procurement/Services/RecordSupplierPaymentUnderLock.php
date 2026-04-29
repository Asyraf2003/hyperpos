<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierPayment\SupplierPayment;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class RecordSupplierPaymentUnderLock
{
    public function __construct(
        private readonly SupplierPaymentWriterPort $writer,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly SupplierInvoiceListProjectionService $projection,
        private readonly SupplierInvoiceReaderPort $invoices,
        private readonly SupplierPaymentReaderPort $payments,
        private readonly RecordSupplierPaymentAuditLog $audit,
        private readonly SupplierInvoiceVoidStatus $voidStatus,
    ) {
    }

    public function execute(
        string $supplierInvoiceId,
        int $amountRupiah,
        DateTimeImmutable $paidAt,
        string $actorId,
    ): Result {
        $this->transactions->begin();

        try {
            $invoice = $this->invoices->getByIdForUpdate(trim($supplierInvoiceId));

            if ($invoice === null) {
                return $this->fail('Nota supplier tidak ditemukan.', ['supplier_payment' => ['SUPPLIER_INVOICE_NOT_FOUND']]);
            }

            if ($this->voidStatus->isVoided($invoice->id())) {
                return $this->fail('Nota supplier yang sudah dibatalkan tidak bisa dimutasi lagi.', ['supplier_invoice' => ['SUPPLIER_INVOICE_VOIDED']]);
            }

            $outstanding = $invoice->grandTotalRupiah()->amount()
                - $this->payments->getTotalPaidBySupplierInvoiceId($invoice->id())->amount();

            if ($outstanding < 1) {
                return $this->fail('Invoice supplier ini sudah lunas.', ['supplier_payment' => ['SUPPLIER_INVOICE_ALREADY_PAID']]);
            }

            if ($amountRupiah > $outstanding) {
                return $this->fail('Nominal pembayaran melebihi outstanding invoice supplier.', ['supplier_payment' => ['PAYMENT_EXCEEDS_OUTSTANDING']]);
            }

            $paymentId = $this->uuid->generate();
            $this->writer->create(SupplierPayment::create(
                $paymentId,
                $invoice->id(),
                Money::fromInt($amountRupiah),
                $paidAt,
                SupplierPayment::PROOF_STATUS_PENDING,
                null
            ));

            $this->audit->record($paymentId, $invoice->id(), $amountRupiah, $outstanding, $paidAt, $actorId);
            $this->projection->syncInvoice($invoice->id());
            $this->transactions->commit();

            return Result::success([
                'supplier_payment_id' => $paymentId,
                'supplier_invoice_id' => $invoice->id(),
                'amount_rupiah' => $amountRupiah,
                'outstanding_rupiah' => $outstanding - $amountRupiah,
            ], 'Pembayaran supplier berhasil dicatat.');
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
