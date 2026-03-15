<?php

declare(strict_types=1);

namespace App\Core\Inventory\Costing;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait ProductInventoryCostingValidation
{
    private static function assertValid(string $pId, Money $avg, Money $val): void
    {
        if (trim($pId) === '') throw new DomainException('Product id wajib ada.');
        if ($avg->isNegative()) throw new DomainException('Average cost tidak boleh negatif.');
        if ($val->isNegative()) throw new DomainException('Inventory value tidak boleh negatif.');
    }
}
