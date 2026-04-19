<?php

declare(strict_types=1);

namespace App\Application\Inventory\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use DateTimeImmutable;

final class StockAdjustmentReversalPreflight
{
    public function __construct(
        private readonly InventoryMovementReaderPort $movements,
    ) {
    }

    public function prepare(
        string $productId,
        string $adjustmentId,
        string $reversedAt,
        string $performedByActorId,
    ): Result {
        $actorId = trim($performedByActorId);

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

        return Result::success([
            'actor_id' => $actorId,
            'date' => $date,
        ]);
    }
}
