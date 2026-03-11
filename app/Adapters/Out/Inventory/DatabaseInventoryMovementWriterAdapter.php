<?php

declare(strict_types=1);

namespace App\Adapters\Out\Inventory;

use App\Core\Inventory\Movement\InventoryMovement;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseInventoryMovementWriterAdapter implements InventoryMovementWriterPort
{
    /**
     * @param list<InventoryMovement> $movements
     */
    public function createMany(array $movements): void
    {
        if ($movements === []) {
            return;
        }

        DB::table('inventory_movements')->insert(
            array_map(
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
            )
        );
    }
}
