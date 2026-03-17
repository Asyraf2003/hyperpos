<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

use App\Application\Reporting\DTO\Concerns\OperationalExpenseSummaryRowAccessors;

final class OperationalExpenseSummaryRow
{
    use OperationalExpenseSummaryRowAccessors;

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

    public function toArray(): array
    {
        return [
            'expense_id' => $this->expenseId,
            'expense_date' => $this->expenseDate,
            'category_id' => $this->categoryId,
            'category_code' => $this->categoryCode,
            'category_name' => $this->categoryName,
            'amount_rupiah' => $this->amountRupiah,
            'description' => $this->description,
            'payment_method' => $this->paymentMethod,
            'reference_no' => $this->referenceNo,
            'status' => $this->status,
        ];
    }
}
