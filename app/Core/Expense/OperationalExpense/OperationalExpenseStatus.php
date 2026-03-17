<?php

declare(strict_types=1);

namespace App\Core\Expense\OperationalExpense;

final class OperationalExpenseStatus
{
    public const DRAFT = 'draft';
    public const POSTED = 'posted';
    public const CANCELLED = 'cancelled';

    public static function all(): array
    {
        return [
            self::DRAFT,
            self::POSTED,
            self::CANCELLED,
        ];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::all(), true);
    }
}
