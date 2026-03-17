<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

final class OperationalExpenseSummaryRow
{
    public function __construct(
        private readonly string $expenseId,
        private readonly string $expenseDate,
        private readonly string $categoryId,
        private readonly string $categoryCode,
        private readonly string $categoryName,
        private readonly int $amountRupiah,
        private readonly string $description,
        private readonly string $paymentMethod,
        private readonly ?string $referenceNo,
        private readonly string $status,
    ) {
    }

    public function expenseId(): string
    {
        return $this->expenseId;
    }

    public function expenseDate(): string
    {
        return $this->expenseDate;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function categoryCode(): string
    {
        return $this->categoryCode;
    }

    public function categoryName(): string
    {
        return $this->categoryName;
    }

    public function amountRupiah(): int
    {
        return $this->amountRupiah;
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

    /**
     * @return array{
     *   expense_id:string,
     *   expense_date:string,
     *   category_id:string,
     *   category_code:string,
     *   category_name:string,
     *   amount_rupiah:int,
     *   description:string,
     *   payment_method:string,
     *   reference_no:?string,
     *   status:string
     * }
     */
    public function toArray(): array
    {
        return [
            'expense_id' => $this->expenseId(),
            'expense_date' => $this->expenseDate(),
            'category_id' => $this->categoryId(),
            'category_code' => $this->categoryCode(),
            'category_name' => $this->categoryName(),
            'amount_rupiah' => $this->amountRupiah(),
            'description' => $this->description(),
            'payment_method' => $this->paymentMethod(),
            'reference_no' => $this->referenceNo(),
            'status' => $this->status(),
        ];
    }
}
