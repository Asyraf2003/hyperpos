<?php

declare(strict_types=1);

namespace App\Ports\Out\Expense;

use App\Core\Expense\OperationalExpense\OperationalExpense;

interface OperationalExpenseWriterPort
{
    public function create(OperationalExpense $operationalExpense): void;
}
