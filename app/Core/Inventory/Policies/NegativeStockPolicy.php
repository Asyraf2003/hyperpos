<?php

declare(strict_types=1);

namespace App\Core\Inventory\Policies;

interface NegativeStockPolicy
{
    public function assertCanIssue(int $availableQty, int $requestedQty): void;
}
