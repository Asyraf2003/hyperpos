<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceListProjectionService;
use App\Application\Procurement\Services\SupplierPaymentPreflight;
use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierPayment\SupplierPayment;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
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
        private readonly SupplierInvoiceListProjectionService $projection,
        private readonly SupplierInvoiceReaderPort $invoices,
        private readonly SupplierPaymentReaderPort $payments,
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
            $actorId = (string) $data['actor_id'];
            $date = $data['paid_at'];

            $this->transactions->begin();
            $started = true;

            $invoice = $this->invoices->getByIdForUpdate(trim($supplierInvoiceId));

            if ($invoice === null) {
                $this->transactions->rollBack();
                $started = false;
                return Result::failure('Nota supplier tidak ditemukan.', ['supplier_payment' => ['SUPPLIER_INVOICE_NOT_FOUND']]);
            }

            $outstanding = $invoice->grandTotalRupiah()->amount() - $this->payments->getTotalPaidBySupplierInvoiceId($invoice->id())->amount();

            if ($outstanding < 1) {
                $this->transactions->rollBack();
                $started = false;
                return Result::failure('Invoice supplier ini sudah lunas.', ['supplier_payment' => ['SUPPLIER_INVOICE_ALREADY_PAID']]);
            }

            if ($amountRupiah > $outstanding) {
                $this->transactions->rollBack();
                $started = false;
                return Result::failure('Nominal pembayaran melebihi outstanding invoice supplier.', ['supplier_payment' => ['PAYMENT_EXCEEDS_OUTSTANDING']]);
            }

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
                'outstanding_before_rupiah' => $outstanding,
                'outstanding_after_rupiah' => $outstanding - $amountRupiah,
                'paid_at' => $date->format('Y-m-d'),
                'performed_by_actor_id' => $actorId,
                'proof_status' => SupplierPayment::PROOF_STATUS_PENDING,
            ]);

            $this->projection->syncInvoice($invoice->id());

            $this->transactions->commit();

            return Result::success([
                'supplier_payment_id' => $paymentId,
                'supplier_invoice_id' => $invoice->id(),
                'amount_rupiah' => $amountRupiah,
                'outstanding_rupiah' => $outstanding - $amountRupiah,
            ], 'Pembayaran supplier berhasil dicatat.');
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
