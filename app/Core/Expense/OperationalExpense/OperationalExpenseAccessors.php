<?php

declare(strict_types=1);

namespace App\Core\Expense\OperationalExpense;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

trait OperationalExpenseAccessors
{
    public function id(): string { return $this->id; }
    public function categoryId(): string { return $this->categoryId; }
    public function categoryCodeSnapshot(): string { return $this->categoryCodeSnapshot; }
    public function categoryNameSnapshot(): string { return $this->categoryNameSnapshot; }
    public function amountRupiah(): Money { return $this->amountRupiah; }
    public function expenseDate(): DateTimeImmutable { return $this->expenseDate; }
    public function description(): string { return $this->description; }
    public function paymentMethod(): string { return $this->paymentMethod; }
    public function status(): string { return $this->status; }
}
