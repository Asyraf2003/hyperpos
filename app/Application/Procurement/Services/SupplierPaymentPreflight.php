<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use DateTimeImmutable;

final class SupplierPaymentPreflight
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $invoices,
        private readonly SupplierPaymentReaderPort $payments,
        private readonly VoidedSupplierInvoiceGuard $voidedGuard,
    ) {
    }

    public function prepare(
        string $supplierInvoiceId,
        int $amountRupiah,
        string $paidAt,
        string $performedByActorId,
    ): Result {
        $voided = $this->voidedGuard->ensureNotVoided($supplierInvoiceId);

        if ($voided->isFailure()) {
            return $voided;
        }

        $invoice = $this->invoices->getById(trim($supplierInvoiceId));

        if ($invoice === null) {
            return Result::failure('Nota supplier tidak ditemukan.', ['supplier_payment' => ['SUPPLIER_INVOICE_NOT_FOUND']]);
        }

        if ($amountRupiah < 1) {
            return Result::failure('Nominal pembayaran wajib lebih dari 0.', ['supplier_payment' => ['INVALID_PAYMENT_AMOUNT']]);
        }

        $actorId = trim($performedByActorId);

        if ($actorId === '') {
            return Result::failure('Actor pembayaran supplier wajib ada.', ['supplier_payment' => ['INVALID_SUPPLIER_PAYMENT']]);
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($paidAt));

        if ($date === false || $date->format('Y-m-d') !== trim($paidAt)) {
            return Result::failure('Tanggal pembayaran wajib valid dengan format Y-m-d.', ['supplier_payment' => ['INVALID_SUPPLIER_PAYMENT']]);
        }

        $outstanding = $invoice->grandTotalRupiah()->amount() - $this->payments->getTotalPaidBySupplierInvoiceId($invoice->id())->amount();

        if ($outstanding < 1) {
            return Result::failure('Invoice supplier ini sudah lunas.', ['supplier_payment' => ['SUPPLIER_INVOICE_ALREADY_PAID']]);
        }

        if ($amountRupiah > $outstanding) {
            return Result::failure('Nominal pembayaran melebihi outstanding invoice supplier.', ['supplier_payment' => ['PAYMENT_EXCEEDS_OUTSTANDING']]);
        }

        return Result::success([
            'invoice' => $invoice,
            'actor_id' => $actorId,
            'paid_at' => $date,
            'amount_rupiah' => $amountRupiah,
            'outstanding_rupiah' => $outstanding,
        ]);
    }
}
