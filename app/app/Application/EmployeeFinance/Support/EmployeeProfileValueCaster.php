<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\Support;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class EmployeeProfileValueCaster
{
    public function toNullableMoney(?int $amount): ?Money
    {
        if ($amount === null || $amount <= 0) {
            return null;
        }

        return Money::fromInt($amount);
    }

    public function parseOptionalDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return new DateTimeImmutable($value);
    }
}
