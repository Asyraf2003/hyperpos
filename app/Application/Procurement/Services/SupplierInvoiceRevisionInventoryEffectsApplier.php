<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Inventory\UseCases\RebuildInventoryCostingProjectionHandler;
use App\Application\Inventory\UseCases\RebuildInventoryProjectionHandler;
use App\Application\Shared\DTO\Result;
use App\Core\Inventory\Movement\InventoryMovement;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;

final class SupplierInvoiceRevisionInventoryEffectsApplier
{
    public function __construct(
        private readonly InventoryMovementWriterPort $inventoryMovements,
        private readonly RebuildInventoryProjectionHandler $rebuildInventoryProjection,
        private readonly RebuildInventoryCostingProjectionHandler $rebuildInventoryCostingProjection,
    ) {
    }

    /**
     * @param list<InventoryMovement> $deltaMovements
     */
    public function apply(array $deltaMovements): Result
    {
        if ($deltaMovements === []) {
            return Result::success();
        }

        $this->inventoryMovements->createMany($deltaMovements);

        $inventoryProjectionResult = $this->rebuildInventoryProjection->handle();
        if ($inventoryProjectionResult->isFailure()) {
            return Result::failure(
                'Proyeksi stok gagal diperbarui setelah revisi nota supplier.',
                ['supplier_invoice' => ['SUPPLIER_INVENTORY_PROJECTION_REBUILD_FAILED']]
            );
        }

        $inventoryCostingResult = $this->rebuildInventoryCostingProjection->handle();
        if ($inventoryCostingResult->isFailure()) {
            return Result::failure(
                'Proyeksi costing gagal diperbarui setelah revisi nota supplier.',
                ['supplier_invoice' => ['SUPPLIER_INVENTORY_COSTING_REBUILD_FAILED']]
            );
        }

        return Result::success();
    }
}
