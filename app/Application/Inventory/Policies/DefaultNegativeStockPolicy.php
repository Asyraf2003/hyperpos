<?php

declare(strict_types=1);

namespace App\Application\Inventory\Policies;

use App\Core\Inventory\Policies\NegativeStockPolicy;
use App\Core\Shared\Exceptions\DomainException;

final class DefaultNegativeStockPolicy implements NegativeStockPolicy
{
    public function assertCanIssue(int $availableQty, int $requestedQty): void
    {
        if ($availableQty < 0) {
            throw new DomainException('Qty tersedia pada inventory tidak boleh negatif.');
        }

        if ($requestedQty <= 0) {
            throw new DomainException('Qty issue inventory harus lebih besar dari nol.');
        }

        if ($requestedQty > $availableQty) {
            throw new DomainException('Stok inventory tidak cukup untuk issue ini.');
        }
    }
}
