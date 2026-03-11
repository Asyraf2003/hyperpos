<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Inventory\ProductInventory\ProductInventory;
use App\Core\Procurement\SupplierReceipt\SupplierReceipt;
use App\Core\Procurement\SupplierReceipt\SupplierReceiptLine;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\Inventory\ProductInventoryWriterPort;
use App\Ports\Out\Procurement\SupplierInvoiceLineReaderPort;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierReceiptLineReaderPort;
use App\Ports\Out\Procurement\SupplierReceiptWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class ReceiveSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $supplierInvoices,
        private readonly SupplierInvoiceLineReaderPort $supplierInvoiceLines,
        private readonly SupplierReceiptLineReaderPort $supplierReceiptLines,
        private readonly SupplierReceiptWriterPort $supplierReceipts,
        private readonly InventoryMovementWriterPort $inventoryMovements,
        private readonly ProductInventoryReaderPort $productInventories,
        private readonly ProductInventoryWriterPort $productInventoryWriter,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     */
    public function handle(
        string $supplierInvoiceId,
        string $tanggalTerima,
        array $lines,
    ): Result {
        $transactionStarted = false;

        try {
            $this->transactions->begin();
            $transactionStarted = true;

            $supplierInvoice = $this->supplierInvoices->getById(trim($supplierInvoiceId));

            if ($supplierInvoice === null) {
                throw new DomainException('Supplier invoice tidak ditemukan.');
            }

            $receiptDate = $this->parseTanggalTerima($tanggalTerima);

            $invoiceLinesById = $this->indexInvoiceLinesById(
                $this->supplierInvoiceLines->getBySupplierInvoiceId($supplierInvoice->id())
            );

            [$receiptLines, $movements] = $this->buildReceiptLinesAndMovements(
                $invoiceLinesById,
                $receiptDate,
                $lines,
            );

            $supplierReceipt = SupplierReceipt::create(
                $this->uuid->generate(),
                $supplierInvoice->id(),
                $receiptDate,
                $receiptLines,
            );

            $this->supplierReceipts->create($supplierReceipt);
            $this->inventoryMovements->createMany($movements);
            $this->applyInventoryProjection($movements);

            $this->transactions->commit();

            return Result::success(
                [
                    'id' => $supplierReceipt->id(),
                    'supplier_invoice_id' => $supplierReceipt->supplierInvoiceId(),
                    'tanggal_terima' => $supplierReceipt->tanggalTerima()->format('Y-m-d'),
                    'lines' => array_map(
                        static fn (SupplierReceiptLine $line): array => [
                            'id' => $line->id(),
                            'supplier_invoice_line_id' => $line->supplierInvoiceLineId(),
                            'qty_diterima' => $line->qtyDiterima(),
                        ],
                        $supplierReceipt->lines(),
                    ),
                    'movements' => array_map(
                        static fn (InventoryMovement $movement): array => [
                            'id' => $movement->id(),
                            'product_id' => $movement->productId(),
                            'movement_type' => $movement->movementType(),
                            'source_type' => $movement->sourceType(),
                            'source_id' => $movement->sourceId(),
                            'tanggal_mutasi' => $movement->tanggalMutasi()->format('Y-m-d'),
                            'qty_delta' => $movement->qtyDelta(),
                            'unit_cost_rupiah' => $movement->unitCostRupiah()->amount(),
                            'total_cost_rupiah' => $movement->totalCostRupiah()->amount(),
                        ],
                        $movements,
                    ),
                ],
                'Supplier receipt berhasil dibuat.'
            );
        } catch (DomainException $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['supplier_receipt' => ['INVALID_SUPPLIER_RECEIPT']]
            );
        } catch (Throwable $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param array<int, array<string, mixed>> $invoiceLines
     * @return array<string, array{
     *     id: string,
     *     supplier_invoice_id: string,
     *     product_id: string,
     *     qty_pcs: int,
     *     line_total_rupiah: int,
     *     unit_cost_rupiah: int
     * }>
     */
    private function indexInvoiceLinesById(array $invoiceLines): array
    {
        $indexed = [];

        foreach ($invoiceLines as $line) {
            if (array_key_exists('id', $line) === false) {
                throw new DomainException('Data supplier invoice line tidak valid.');
            }

            $indexed[(string) $line['id']] = [
                'id' => (string) $line['id'],
                'supplier_invoice_id' => (string) $line['supplier_invoice_id'],
                'product_id' => (string) $line['product_id'],
                'qty_pcs' => (int) $line['qty_pcs'],
                'line_total_rupiah' => (int) $line['line_total_rupiah'],
                'unit_cost_rupiah' => (int) $line['unit_cost_rupiah'],
            ];
        }

        return $indexed;
    }

    /**
     * @param array<string, array{
     *     id: string,
     *     supplier_invoice_id: string,
     *     product_id: string,
     *     qty_pcs: int,
     *     line_total_rupiah: int,
     *     unit_cost_rupiah: int
     * }> $invoiceLinesById
     * @param array<int, array<string, mixed>> $lines
     * @return array{0: list<SupplierReceiptLine>, 1: list<InventoryMovement>}
     */
    private function buildReceiptLinesAndMovements(
        array $invoiceLinesById,
        DateTimeImmutable $receiptDate,
        array $lines,
    ): array {
        if ($lines === []) {
            throw new DomainException('Supplier receipt minimal harus memiliki satu line.');
        }

        $receiptLines = [];
        $movements = [];

        foreach ($lines as $line) {
            if (array_key_exists('supplier_invoice_line_id', $line) === false) {
                throw new DomainException('Supplier invoice line id pada supplier receipt wajib ada.');
            }

            if (array_key_exists('qty_diterima', $line) === false) {
                throw new DomainException('Qty diterima pada supplier receipt wajib ada.');
            }

            $supplierInvoiceLineId = trim((string) $line['supplier_invoice_line_id']);
            $qtyDiterima = (int) $line['qty_diterima'];

            if (array_key_exists($supplierInvoiceLineId, $invoiceLinesById) === false) {
                throw new DomainException('Supplier invoice line tidak ditemukan untuk receipt ini.');
            }

            $invoiceLine = $invoiceLinesById[$supplierInvoiceLineId];
            $receivedQtySoFar = $this->supplierReceiptLines
                ->getReceivedQtyBySupplierInvoiceLineId($supplierInvoiceLineId);

            if (($receivedQtySoFar + $qtyDiterima) > $invoiceLine['qty_pcs']) {
                throw new DomainException('Qty diterima melebihi qty pada supplier invoice line.');
            }

            $receiptLine = SupplierReceiptLine::create(
                $this->uuid->generate(),
                $supplierInvoiceLineId,
                $qtyDiterima,
            );

            $movement = InventoryMovement::create(
                $this->uuid->generate(),
                $invoiceLine['product_id'],
                'stock_in',
                'supplier_receipt_line',
                $receiptLine->id(),
                $receiptDate,
                $qtyDiterima,
                Money::fromInt($invoiceLine['unit_cost_rupiah']),
            );

            $receiptLines[] = $receiptLine;
            $movements[] = $movement;
        }

        return [$receiptLines, $movements];
    }

    /**
     * @param list<InventoryMovement> $movements
     */
    private function applyInventoryProjection(array $movements): void
    {
        /** @var array<string, ProductInventory> $inventoriesByProduct */
        $inventoriesByProduct = [];

        foreach ($movements as $movement) {
            $productId = $movement->productId();

            if (array_key_exists($productId, $inventoriesByProduct) === false) {
                $inventoriesByProduct[$productId] = $this->productInventories->getByProductId($productId)
                    ?? ProductInventory::create($productId, 0);
            }

            $inventoriesByProduct[$productId]->increase($movement->qtyDelta());
        }

        foreach ($inventoriesByProduct as $inventory) {
            $this->productInventoryWriter->upsert($inventory);
        }
    }

    private function parseTanggalTerima(string $tanggalTerima): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tanggalTerima));

        if ($parsed === false || $parsed->format('Y-m-d') !== trim($tanggalTerima)) {
            throw new DomainException('Tanggal terima wajib berupa tanggal yang valid dengan format Y-m-d.');
        }

        return $parsed;
    }
}
