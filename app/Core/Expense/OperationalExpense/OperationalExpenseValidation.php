<?php

declare(strict_types=1);

namespace App\Core\Expense\OperationalExpense;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait OperationalExpenseValidation
{
    private static function assertValid(
        string $id,
        string $categoryId,
        Money $amountRupiah,
        string $description,
        string $paymentMethod,
        string $status,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Operational expense id wajib ada.');
        }

        if (trim($categoryId) === '') {
            throw new DomainException('Category id pada operational expense wajib ada.');
        }

        if ($amountRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Amount rupiah pada operational expense harus lebih besar dari nol.');
        }

        if (trim($description) === '') {
            throw new DomainException('Deskripsi operational expense wajib ada.');
        }

        if (trim($paymentMethod) === '') {
            throw new DomainException('Payment method operational expense wajib ada.');
        }

        if (OperationalExpenseStatus::isValid($status) === false) {
            throw new DomainException('Status operational expense tidak valid.');
        }
    }

    private static function normalizeReferenceNo(?string $referenceNo): ?string
    {
        $normalized = trim((string) $referenceNo);

        return $normalized === '' ? null : $normalized;
    }
}
