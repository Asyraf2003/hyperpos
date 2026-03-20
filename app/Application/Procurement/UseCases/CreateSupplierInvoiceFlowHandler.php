<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Inventory\Services\InventoryProjectionService;
use App\Application\Procurement\Services\SupplierInvoiceFactory;
use App\Application\Procurement\Services\SupplierService;
use App\Application\Shared\DTO\Result;
use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierReceipt\SupplierReceipt;
use App\Core\Procurement\SupplierReceipt\SupplierReceiptLine;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\Procurement\SupplierReceiptWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class CreateSupplierInvoiceFlowHandler
{
    public function __construct(
        private SupplierInvoiceWriterPort $invoiceWriter,
        private SupplierReceiptWriterPort $receiptWriter,
        private InventoryMovementWriterPort $movementWriter,
        private TransactionManagerPort $transactions,
        private UuidPort $uuid,
        private SupplierService $supplierService,
        private SupplierInvoiceFactory $invoiceFactory,
        private InventoryProjectionService $projection,
        private AuditLogPort $audit
    ) {
    }

    public function handle(
        string $pt,
        string $tglKirim,
        array $lines,
        bool $autoRec = true,
        ?string $tglTerima = null
    ): Result {
        $started = false;

        try {
            $dateKirim = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tglKirim))
                ?: throw new DomainException('Tgl kirim tidak valid.');

            $dateTerima = $autoRec
                ? (
                    DateTimeImmutable::createFromFormat('!Y-m-d', trim($tglTerima ?? $tglKirim))
                    ?: throw new DomainException('Tgl terima tidak valid.')
                )
                : null;

            $this->transactions->begin();
            $started = true;

            $supplier = $this->supplierService->resolve($pt);
            $invoice = SupplierInvoice::create(
                $this->uuid->generate(),
                $supplier->id(),
                $dateKirim,
                $this->invoiceFactory->makeLines($lines)
            );

            $this->invoiceWriter->create($invoice);

            if ($autoRec && $dateTerima !== null) {
                $this->processAutoReceive($invoice, $dateTerima);
            }

            $this->audit->record('supplier_invoice_flow_completed', [
                'invoice_id' => $invoice->id(),
                'supplier_name' => $supplier->namaPtPengirim(),
                'grand_total' => $invoice->grandTotalRupiah()->amount(),
                'auto_received' => $autoRec,
            ]);

            $this->transactions->commit();

            return Result::success(
                ['id' => $invoice->id()],
                'Flow Supplier Invoice Berhasil.'
            );
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['supplier_invoice' => ['INVALID_SUPPLIER_INVOICE']]
            );
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    private function processAutoReceive(SupplierInvoice $inv, DateTimeImmutable $date): void
    {
        $receiptLines = array_map(
            fn ($line) => SupplierReceiptLine::create(
                $this->uuid->generate(),
                $line->id(),
                $line->qtyPcs()
            ),
            $inv->lines()
        );

        $receipt = SupplierReceipt::create(
            $this->uuid->generate(),
            $inv->id(),
            $date,
            $receiptLines
        );

        $movements = array_map(
            fn ($receiptLine, $invoiceLine) => InventoryMovement::create(
                $this->uuid->generate(),
                $invoiceLine->productId(),
                'stock_in',
                'supplier_receipt_line',
                $receiptLine->id(),
                $date,
                $receiptLine->qtyDiterima(),
                $invoiceLine->unitCostRupiah()
            ),
            $receiptLines,
            $inv->lines()
        );

        $this->receiptWriter->create($receipt);
        $this->movementWriter->createMany($movements);
        $this->projection->applyMovements($movements);
    }
}
