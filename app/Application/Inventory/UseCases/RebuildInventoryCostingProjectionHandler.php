<?php

declare(strict_types=1);

namespace App\Application\Inventory\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Inventory\Costing\ProductInventoryCosting;
use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use App\Ports\Out\Inventory\ProductInventoryCostingProjectionWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class RebuildInventoryCostingProjectionHandler
{
    public function __construct(
        private readonly InventoryMovementReaderPort $inventoryMovements,
        private readonly ProductInventoryCostingProjectionWriterPort $inventoryCostingProjection,
        private readonly TransactionManagerPort $transactions,
    ) {
    }

    public function handle(): Result
    {
        $transactionStarted = false;

        try {
            $this->transactions->begin();
            $transactionStarted = true;

            $movements = $this->inventoryMovements->getAll();
            $costings = $this->rebuildProjection($movements);

            $this->inventoryCostingProjection->replaceAll($costings);

            $this->transactions->commit();

            return Result::success(
                [
                    'total_movements' => count($movements),
                    'total_products' => count($costings),
                    'products' => array_map(
                        static fn (ProductInventoryCosting $costing): array => [
                            'product_id' => $costing->productId(),
                            'avg_cost_rupiah' => $costing->avgCostRupiah()->amount(),
                            'inventory_value_rupiah' => $costing->inventoryValueRupiah()->amount(),
                        ],
                        $costings,
                    ),
                ],
                'Inventory costing projection berhasil dibangun ulang.'
            );
        } catch (DomainException $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['inventory_costing' => ['INVALID_INVENTORY_COSTING_PROJECTION']]
            );
        } catch (Throwable $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param list<InventoryMovement> $movements
     * @return list<ProductInventoryCosting>
     */
    private function rebuildProjection(array $movements): array
    {
        /** @var array<string, array{qty: int, inventory_value_rupiah: int}> $summariesByProduct */
        $summariesByProduct = [];

        foreach ($movements as $movement) {
            if ($movement->movementType() !== 'stock_in') {
                continue;
            }

            $productId = $movement->productId();

            if (array_key_exists($productId, $summariesByProduct) === false) {
                $summariesByProduct[$productId] = [
                    'qty' => 0,
                    'inventory_value_rupiah' => 0,
                ];
            }

            $summariesByProduct[$productId]['qty'] += $movement->qtyDelta();
            $summariesByProduct[$productId]['inventory_value_rupiah'] += $movement->totalCostRupiah()->amount();
        }

        ksort($summariesByProduct);

        $costings = [];

        foreach ($summariesByProduct as $productId => $summary) {
            if ($summary['qty'] <= 0) {
                continue;
            }

            $inventoryValueRupiah = Money::fromInt($summary['inventory_value_rupiah']);
            $avgCostRupiah = Money::fromInt(
                intdiv($inventoryValueRupiah->amount(), $summary['qty'])
            );

            $costings[] = ProductInventoryCosting::create(
                $productId,
                $avgCostRupiah,
                $inventoryValueRupiah,
            );
        }

        return $costings;
    }
}
