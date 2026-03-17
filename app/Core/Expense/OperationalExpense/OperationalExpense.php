<?php

declare(strict_types=1);

namespace App\Core\Expense\OperationalExpense;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class OperationalExpense
{
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

    public function id(): string
    {
        return $this->id;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function amountRupiah(): Money
    {
        return $this->amountRupiah;
    }

    public function expenseDate(): DateTimeImmutable
    {
        return $this->expenseDate;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function paymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function referenceNo(): ?string
    {
        return $this->referenceNo;
    }

    public function status(): string
    {
        return $this->status;
    }

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
