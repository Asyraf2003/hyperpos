<?php

declare(strict_types=1);

namespace App\Application\Inventory\UseCases;

use App\Application\Inventory\Services\ReverseIssuedInventoryOperation;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\{AuditLogPort, TransactionManagerPort};
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use DateTimeImmutable;
use Throwable;

final class ReverseStockAdjustmentHandler
{
    public function __construct(
        private readonly ReverseIssuedInventoryOperation $operation,
        private readonly InventoryMovementReaderPort $movements,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(
        string $productId,
        string $adjustmentId,
        string $reversedAt,
        string $performedByActorId,
    ): Result {
        $actorId = trim($performedByActorId);
        $started = false;

        try {
            if ($actorId === '') {
                throw new DomainException('Actor reverse stock adjustment wajib ada.');
            }

            $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($reversedAt));

            if ($date === false || $date->format('Y-m-d') !== trim($reversedAt)) {
                throw new DomainException('Tanggal reverse stock adjustment wajib valid dengan format Y-m-d.');
            }

            $issued = array_values(array_filter(
                $this->movements->getBySource('stock_adjustment', trim($adjustmentId)),
                fn ($movement): bool => $movement->productId() === trim($productId) && $movement->qtyDelta() < 0,
            ));

            if ($issued === []) {
                return Result::failure(
                    'Stock adjustment tidak ditemukan.',
                    ['stock_adjustment_reversal' => ['STOCK_ADJUSTMENT_NOT_FOUND']],
                );
            }

            if ($this->movements->getBySource('stock_adjustment_reversal', trim($adjustmentId)) !== []) {
                return Result::failure(
                    'Stock adjustment ini sudah direverse.',
                    ['stock_adjustment_reversal' => ['STOCK_ADJUSTMENT_ALREADY_REVERSED']],
                );
            }

            $this->transactions->begin();
            $started = true;

            $reversed = $this->operation->execute(
                'stock_adjustment',
                trim($adjustmentId),
                $date,
                'stock_adjustment_reversal',
            );

            if ($reversed === []) {
                throw new DomainException('Stock adjustment tidak bisa direverse karena projection inventory tidak lengkap.');
            }

            $this->audit->record('stock_adjustment_reversed', [
                'adjustment_id' => trim($adjustmentId),
                'product_id' => trim($productId),
                'reversed_at' => $date->format('Y-m-d'),
                'performed_by_actor_id' => $actorId,
                'reversed_movement_count' => count($reversed),
            ]);

            $this->transactions->commit();

            return Result::success([
                'adjustment_id' => trim($adjustmentId),
                'reversed_movement_count' => count($reversed),
            ], 'Stock adjustment berhasil direverse.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['stock_adjustment_reversal' => ['INVALID_STOCK_ADJUSTMENT_REVERSAL']],
            );
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
