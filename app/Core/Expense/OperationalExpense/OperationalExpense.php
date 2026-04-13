<?php

declare(strict_types=1);

namespace App\Core\Expense\OperationalExpense;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class OperationalExpense
{
    use OperationalExpenseValidation;
    use OperationalExpenseAccessors;

    private function __construct(
        private string $id,
        private string $categoryId,
        private string $categoryCodeSnapshot,
        private string $categoryNameSnapshot,
        private Money $amountRupiah,
        private DateTimeImmutable $expenseDate,
        private string $description,
        private string $paymentMethod,
    ) {
    }

    public static function create(
        string $id,
        string $categoryId,
        string $categoryCodeSnapshot,
        string $categoryNameSnapshot,
        Money $amountRupiah,
        DateTimeImmutable $expenseDate,
        string $description,
        string $paymentMethod,
    ): self {
        return self::build(
            $id,
            $categoryId,
            $categoryCodeSnapshot,
            $categoryNameSnapshot,
            $amountRupiah,
            $expenseDate,
            $description,
            $paymentMethod,
        );
    }

    public static function rehydrate(
        string $id,
        string $categoryId,
        string $categoryCodeSnapshot,
        string $categoryNameSnapshot,
        Money $amountRupiah,
        DateTimeImmutable $expenseDate,
        string $description,
        string $paymentMethod,
    ): self {
        return self::build(
            $id,
            $categoryId,
            $categoryCodeSnapshot,
            $categoryNameSnapshot,
            $amountRupiah,
            $expenseDate,
            $description,
            $paymentMethod,
        );
    }

    private static function build(
        string $id,
        string $categoryId,
        string $categoryCodeSnapshot,
        string $categoryNameSnapshot,
        Money $amountRupiah,
        DateTimeImmutable $expenseDate,
        string $description,
        string $paymentMethod,
    ): self {
        self::assertValid($id, $categoryId, $amountRupiah, $description, $paymentMethod);

        return new self(
            trim($id),
            trim($categoryId),
            trim($categoryCodeSnapshot),
            trim($categoryNameSnapshot),
            $amountRupiah,
            $expenseDate,
            trim($description),
            trim($paymentMethod),
        );
    }
}
