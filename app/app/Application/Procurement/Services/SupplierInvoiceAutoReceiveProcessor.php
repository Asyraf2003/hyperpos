<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Inventory\Services\InventoryProjectionService;
use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierReceipt\SupplierReceipt;
use App\Core\Procurement\SupplierReceipt\SupplierReceiptLine;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
use App\Ports\Out\Procurement\SupplierReceiptWriterPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class SupplierInvoiceAutoReceiveProcessor
{
    public function __construct(
        private SupplierReceiptWriterPort $receiptWriter,
        private InventoryMovementWriterPort $movementWriter,
        private UuidPort $uuid,
        private InventoryProjectionService $projection,
    ) {
    }

    public function process(SupplierInvoice $invoice, DateTimeImmutable $tanggalTerima): void
    {
        $receiptLines = array_map(
            fn ($line) => SupplierReceiptLine::create(
                $this->uuid->generate(),
                $line->id(),
                $line->qtyPcs()
            ),
            $invoice->lines()
        );

        $receipt = SupplierReceipt::create(
            $this->uuid->generate(),
            $invoice->id(),
            $tanggalTerima,
            $receiptLines
        );

        $movements = array_map(
            fn ($receiptLine, $invoiceLine) => InventoryMovement::create(
                $this->uuid->generate(),
                $invoiceLine->productId(),
                'stock_in',
                'supplier_receipt_line',
                $receiptLine->id(),
                $tanggalTerima,
                $receiptLine->qtyDiterima(),
                $invoiceLine->unitCostRupiah()
            ),
            $receiptLines,
            $invoice->lines()
        );

        $this->receiptWriter->create($receipt);
        $this->movementWriter->createMany($movements);
        $this->projection->applyMovements($movements);
    }
}
