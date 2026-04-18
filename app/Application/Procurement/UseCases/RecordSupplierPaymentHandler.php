<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\VoidedSupplierInvoiceGuard;
use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierPayment\SupplierPayment;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class RecordSupplierPaymentHandler
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $invoices,
        private readonly SupplierPaymentReaderPort $payments,
        private readonly SupplierPaymentWriterPort $writer,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
        private readonly VoidedSupplierInvoiceGuard $voidedGuard,
    ) {}

    public function handle(string $supplierInvoiceId, int $amountRupiah, string $paidAt, string $performedByActorId): Result
    {
        $voided = $this->voidedGuard->ensureNotVoided($supplierInvoiceId);

        if ($voided->isFailure()) {
            return $voided;
        }

        $started = false;
        try {
            if (($invoice = $this->invoices->getById(trim($supplierInvoiceId))) === null) {
                return Result::failure('Nota supplier tidak ditemukan.', ['supplier_payment' => ['SUPPLIER_INVOICE_NOT_FOUND']]);
            }

            if ($amountRupiah < 1) {
                return Result::failure('Nominal pembayaran wajib lebih dari 0.', ['supplier_payment' => ['INVALID_PAYMENT_AMOUNT']]);
            }

            if (($actorId = trim($performedByActorId)) === '') {
                throw new DomainException('Actor pembayaran supplier wajib ada.');
            }

            if (($date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($paidAt))) === false || $date->format('Y-m-d') !== trim($paidAt)) {
                throw new DomainException('Tanggal pembayaran wajib valid dengan format Y-m-d.');
            }

            $out = $invoice->grandTotalRupiah()->amount() - $this->payments->getTotalPaidBySupplierInvoiceId($invoice->id())->amount();

            if ($out < 1) {
                return Result::failure('Invoice supplier ini sudah lunas.', ['supplier_payment' => ['SUPPLIER_INVOICE_ALREADY_PAID']]);
            }

            if ($amountRupiah > $out) {
                return Result::failure('Nominal pembayaran melebihi outstanding invoice supplier.', ['supplier_payment' => ['PAYMENT_EXCEEDS_OUTSTANDING']]);
            }

            $this->transactions->begin();
            $started = true;

            $paymentId = $this->uuid->generate();

            $this->writer->create(
                SupplierPayment::create(
                    $paymentId,
                    $invoice->id(),
                    Money::fromInt($amountRupiah),
                    $date,
                    SupplierPayment::PROOF_STATUS_PENDING,
                    null
                )
            );

            $this->audit->record('supplier_payment_recorded', [
                'supplier_payment_id' => $paymentId,
                'supplier_invoice_id' => $invoice->id(),
                'amount_rupiah' => $amountRupiah,
                'outstanding_before_rupiah' => $out,
                'outstanding_after_rupiah' => $out - $amountRupiah,
                'paid_at' => $date->format('Y-m-d'),
                'performed_by_actor_id' => $actorId,
                'proof_status' => SupplierPayment::PROOF_STATUS_PENDING,
            ]);

            $this->transactions->commit();

            return Result::success([
                'supplier_payment_id' => $paymentId,
                'supplier_invoice_id' => $invoice->id(),
                'amount_rupiah' => $amountRupiah,
                'outstanding_rupiah' => $out - $amountRupiah,
            ], 'Pembayaran supplier berhasil dicatat.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure($e->getMessage(), ['supplier_payment' => ['INVALID_SUPPLIER_PAYMENT']]);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
