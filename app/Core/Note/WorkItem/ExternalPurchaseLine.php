<?php

declare(strict_types=1);

namespace App\Core\Note\WorkItem;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class ExternalPurchaseLine
{
    private function __construct(
        private string $id,
        private string $costDescription,
        private Money $unitCostRupiah,
        private int $qty,
        private Money $lineTotalRupiah,
    ) {}

    public static function create(string $id, string $desc, Money $unitCost, int $qty): self
    {
        self::assertValid($id, $desc, $unitCost, $qty);
        return new self(trim($id), trim($desc), $unitCost, $qty, $unitCost->multiply($qty));
    }

    public static function rehydrate(string $id, string $desc, Money $unitCost, int $qty): self
    {
        self::assertValid($id, $desc, $unitCost, $qty);
        return new self(trim($id), trim($desc), $unitCost, $qty, $unitCost->multiply($qty));
    }

    public function id(): string { return $this->id; }
    public function costDescription(): string { return $this->costDescription; }
    public function unitCostRupiah(): Money { return $this->unitCostRupiah; }
    public function qty(): int { return $this->qty; }
    public function lineTotalRupiah(): Money { return $this->lineTotalRupiah; }

    private static function assertValid(string $id, string $desc, Money $unitCost, int $qty): void
    {
        if (trim($id) === '') throw new DomainException('ID wajib ada.');
        if (trim($desc) === '') throw new DomainException('Cost description wajib ada.');
        if (!$unitCost->greaterThan(Money::zero())) throw new DomainException('Unit cost harus > 0.');
        if ($qty <= 0) throw new DomainException('Qty harus > 0.');
    }
}
