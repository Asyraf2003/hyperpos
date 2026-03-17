<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO\Concerns;

trait OperationalExpenseSummaryRowAccessors
{
    public function expenseId(): string { return $this->expenseId; }

    public function expenseDate(): string { return $this->expenseDate; }

    public function categoryId(): string { return $this->categoryId; }

    public function categoryCode(): string { return $this->categoryCode; }

    public function categoryName(): string { return $this->categoryName; }

    public function amountRupiah(): int { return $this->amountRupiah; }

    public function description(): string { return $this->description; }

    public function paymentMethod(): string { return $this->paymentMethod; }

    public function referenceNo(): ?string { return $this->referenceNo; }

    public function status(): string { return $this->status; }
}
