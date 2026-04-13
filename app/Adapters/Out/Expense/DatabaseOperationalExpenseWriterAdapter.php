<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense;

use App\Core\Expense\OperationalExpense\OperationalExpense;
use App\Ports\Out\Expense\OperationalExpenseWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseOperationalExpenseWriterAdapter implements OperationalExpenseWriterPort
{
    public function create(OperationalExpense $operationalExpense): void
    {
        $now = Carbon::now();

        DB::table('operational_expenses')->insert([
            'id' => $operationalExpense->id(),
            'category_id' => $operationalExpense->categoryId(),
            'category_code_snapshot' => $operationalExpense->categoryCodeSnapshot(),
            'category_name_snapshot' => $operationalExpense->categoryNameSnapshot(),
            'amount_rupiah' => $operationalExpense->amountRupiah()->amount(),
            'expense_date' => $operationalExpense->expenseDate()->format('Y-m-d'),
            'description' => $operationalExpense->description(),
            'payment_method' => $operationalExpense->paymentMethod(),
            'status' => 'posted',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }
}
