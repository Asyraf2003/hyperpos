<?php

declare(strict_types=1);

namespace App\Core\Expense\ExpenseCategory;

use App\Core\Shared\Exceptions\DomainException;

trait ExpenseCategoryValidation
{
    private static function assertValid(
        string $id,
        string $code,
        string $name,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Expense category id wajib ada.');
        }

        if (trim($code) === '') {
            throw new DomainException('Kode expense category wajib ada.');
        }

        if (trim($name) === '') {
            throw new DomainException('Nama expense category wajib ada.');
        }
    }

    private static function normalizeDescription(?string $description): ?string
    {
        $normalized = trim((string) $description);

        return $normalized === '' ? null : $normalized;
    }
}
