<?php

declare(strict_types=1);

namespace App\Application\Inventory\UseCases;

use App\Application\Inventory\Services\ReverseIssuedInventoryOperation;
use App\Application\Inventory\Services\StockAdjustmentReversalPreflight;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\{AuditLogPort, TransactionManagerPort};
use Throwable;

final class ReverseStockAdjustmentHandler
{
    public function __construct(
        private readonly ReverseIssuedInventoryOperation $operation,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
        private readonly StockAdjustmentReversalPreflight $preflight,
    ) {
    }

    public function handle(
        string $productId,
        string $adjustmentId,
        string $reversedAt,
        string $performedByActorId,
    ): Result {
        $started = false;

        try {
            $prepared = $this->preflight->prepare($productId, $adjustmentId, $reversedAt, $performedByActorId);

            if ($prepared->isFailure()) {
                return $prepared;
            }

            $data = $prepared->data();
            $date = $data['date'];
            $actorId = (string) $data['actor_id'];

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
