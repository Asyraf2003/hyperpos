<?php

declare(strict_types=1);

namespace App\Core\Inventory\Movement;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

trait InventoryMovementState
{
    private function __construct(
        private string $id,
        private string $productId,
        private string $movementType,
        private string $sourceType,
        private string $sourceId,
        private DateTimeImmutable $tanggalMutasi,
        private int $qtyDelta,
        private Money $unitCostRupiah,
        private Money $totalCostRupiah,
    ) {}

    public function id(): string { return $this->id; }
    public function productId(): string { return $this->productId; }
    public function movementType(): string { return $this->movementType; }
    public function sourceType(): string { return $this->sourceType; }
    public function sourceId(): string { return $this->sourceId; }
    public function tanggalMutasi(): DateTimeImmutable { return $this->tanggalMutasi; }
    public function qtyDelta(): int { return $this->qtyDelta; }
    public function unitCostRupiah(): Money { return $this->unitCostRupiah; }
    public function totalCostRupiah(): Money { return $this->totalCostRupiah; }
}
