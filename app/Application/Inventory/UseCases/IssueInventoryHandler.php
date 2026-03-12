<?php

declare(strict_types=1);

namespace App\Application\Inventory\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Inventory\Movement\InventoryMovement;
use App\Core\Inventory\Policies\NegativeStockPolicy;
use App\Core\Inventory\ProductInventory\ProductInventory;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Inventory\InventoryMovementWriterPort;
use App\Ports\Out\Inventory\ProductInventoryCostingReaderPort;
use App\Ports\Out\Inventory\ProductInventoryCostingWriterPort;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\Inventory\ProductInventoryWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class IssueInventoryHandler
{
    public function __construct(
        private readonly ProductInventoryReaderPort $productInventories,
        private readonly ProductInventoryWriterPort $productInventoryWriter,
        private readonly ProductInventoryCostingReaderPort $productInventoryCostings,
        private readonly ProductInventoryCostingWriterPort $productInventoryCostingWriter,
        private readonly InventoryMovementWriterPort $inventoryMovements,
        private readonly NegativeStockPolicy $negativeStockPolicy,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
    ) {
    }

    public function handle(
        string $productId,
        int $qtyIssue,
        string $tanggalMutasi,
        string $sourceType,
        string $sourceId,
    ): Result {
        try {
            $normalizedProductId = $this->normalizeRequired($productId, 'Product id pada inventory issue wajib ada.');
            $normalizedSourceType = $this->normalizeRequired($sourceType, 'Source type pada inventory issue wajib ada.');
            $normalizedSourceId = $this->normalizeRequired($sourceId, 'Source id pada inventory issue wajib ada.');

            if ($qtyIssue <= 0) {
                throw new DomainException('Qty issue inventory harus lebih besar dari nol.');
            }

            $movementDate = $this->parseTanggalMutasi($tanggalMutasi);
        } catch (DomainException $e) {
            return Result::failure(
                $e->getMessage(),
                ['inventory' => ['INVALID_INVENTORY_ISSUE']]
            );
        }

        $transactionStarted = false;

        try {
            $this->transactions->begin();
            $transactionStarted = true;

            $inventory = $this->productInventories->getByProductId($normalizedProductId)
                ?? ProductInventory::create($normalizedProductId, 0);

            $availableQty = $inventory->qtyOnHand();

            $this->negativeStockPolicy->assertCanIssue($availableQty, $qtyIssue);

            $costing = $this->productInventoryCostings->getByProductId($normalizedProductId);

            if ($costing === null) {
                throw new DomainException('Inventory costing projection tidak ditemukan untuk product ini.');
            }

            $movement = InventoryMovement::create(
                $this->uuid->generate(),
                $normalizedProductId,
                'stock_out',
                $normalizedSourceType,
                $normalizedSourceId,
                $movementDate,
                -$qtyIssue,
                $costing->avgCostRupiah(),
            );

            $inventory->decrease($qtyIssue);
            $costing->applyOutgoingStock($availableQty, $qtyIssue);

            $this->inventoryMovements->createMany([$movement]);
            $this->productInventoryWriter->upsert($inventory);
            $this->productInventoryCostingWriter->upsert($costing);

            $this->transactions->commit();

            return Result::success(
                [
                    'movement' => [
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
                    'product_inventory' => [
                        'product_id' => $inventory->productId(),
                        'qty_on_hand' => $inventory->qtyOnHand(),
                    ],
                    'product_inventory_costing' => [
                        'product_id' => $costing->productId(),
                        'avg_cost_rupiah' => $costing->avgCostRupiah()->amount(),
                        'inventory_value_rupiah' => $costing->inventoryValueRupiah()->amount(),
                    ],
                ],
                'Inventory issue berhasil dibuat.'
            );
        } catch (DomainException $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['inventory' => ['INVALID_INVENTORY_ISSUE']]
            );
        } catch (Throwable $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    private function parseTanggalMutasi(string $tanggalMutasi): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tanggalMutasi));

        if ($parsed === false || $parsed->format('Y-m-d') !== trim($tanggalMutasi)) {
            throw new DomainException('Tanggal mutasi inventory issue wajib berupa tanggal yang valid dengan format Y-m-d.');
        }

        return $parsed;
    }

    private function normalizeRequired(string $value, string $message): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new DomainException($message);
        }

        return $normalized;
    }
}
