<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use App\Ports\Out\Procurement\SupplierReceiptLineReaderPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class SupplierReceiptReversalDeltaMovementsBuilder
{
    public function __construct(
        private readonly InventoryMovementReaderPort $movements,
        private readonly SupplierReceiptLineReaderPort $receiptLines,
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @return list<InventoryMovement>
     */
    public function build(string $supplierReceiptId, DateTimeImmutable $movementDate): array
    {
        $receiptLineIds = $this->receiptLines->getIdsBySupplierReceiptId($supplierReceiptId);

        if ($receiptLineIds === []) {
            throw new DomainException('Supplier receipt line tidak ditemukan.');
        }

        $deltaMovements = [];

        foreach ($receiptLineIds as $receiptLineId) {
            foreach ($this->movements->getBySource('supplier_receipt_line', $receiptLineId) as $movement) {
                if ($movement->movementType() !== 'stock_in' || $movement->qtyDelta() <= 0) {
                    continue;
                }

                $deltaMovements[] = InventoryMovement::create(
                    $this->uuid->generate(),
                    $movement->productId(),
                    'stock_out',
                    'supplier_receipt_reversal_line',
                    $receiptLineId,
                    $movementDate,
                    -abs($movement->qtyDelta()),
                    $movement->unitCostRupiah(),
                );
            }
        }

        if ($deltaMovements === []) {
            throw new DomainException('Movement penerimaan supplier tidak ditemukan.');
        }

        return $deltaMovements;
    }
}
