<?php

declare(strict_types=1);

namespace App\Core\Expense\OperationalExpense;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class OperationalExpense
{
    use OperationalExpenseValidation;

    private function __construct(
        private string $id,
        private string $categoryId,
        private Money $amountRupiah,
        private DateTimeImmutable $expenseDate,
        private string $description,
        private string $paymentMethod,
        private ?string $referenceNo,
        private string $status,
    ) {
    }

    public static function create(
        string $id,
        string $categoryId,
        Money $amountRupiah,
        DateTimeImmutable $expenseDate,
        string $description,
        string $paymentMethod,
        ?string $referenceNo = null,
        string $status = OperationalExpenseStatus::POSTED,
    ): self {
        self::assertValid(
            $id,
            $categoryId,
            $amountRupiah,
            $description,
            $paymentMethod,
            $status,
        );

        return new self(
            trim($id),
            trim($categoryId),
            $amountRupiah,
            $expenseDate,
            trim($description),
            trim($paymentMethod),
            self::normalizeReferenceNo($referenceNo),
            $status,
        );
    }

    public static function rehydrate(
        string $id,
        string $categoryId,
        Money $amountRupiah,
        DateTimeImmutable $expenseDate,
        string $description,
        string $paymentMethod,
        ?string $referenceNo,
        string $status,
    ): self {
        self::assertValid(
            $id,
            $categoryId,
            $amountRupiah,
            $description,
            $paymentMethod,
            $status,
        );

        return new self(
            trim($id),
            trim($categoryId),
            $amountRupiah,
            $expenseDate,
            trim($description),
            trim($paymentMethod),
            self::normalizeReferenceNo($referenceNo),
            $status,
        );
    }

    public function id(): string { return $this->id; }
    public function categoryId(): string { return $this->categoryId; }
    public function amountRupiah(): Money { return $this->amountRupiah; }
    public function expenseDate(): DateTimeImmutable { return $this->expenseDate; }
    public function description(): string { return $this->description; }
    public function paymentMethod(): string { return $this->paymentMethod; }
    public function referenceNo(): ?string { return $this->referenceNo; }
    public function status(): string { return $this->status; }
}
