<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Expense\UseCases\RecordOperationalExpenseHandler;
use App\Application\Expense\UseCases\SoftDeleteOperationalExpenseHandler;
use App\Application\Reporting\UseCases\GetOperationalExpenseSummaryHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class OperationalExpenseSummaryHardeningFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_expense_summary_period_parity_matches_expected_totals(): void
    {
        $this->seedExpenseCategory('expense-category-1', 'LISTRIK', 'Listrik', true);
        $this->seedExpenseCategory('expense-category-2', 'MAKAN', 'Makan', true);

        $this->seedOperationalExpense('expense-1', 'expense-category-1', 100000, '2030-01-06', 'Bayar listrik', 'cash', null);
        $this->seedOperationalExpense('expense-2', 'expense-category-2', 25000, '2030-01-07', 'Makan tim', 'tf', null);
        $this->seedOperationalExpense('expense-3', 'expense-category-1', 75000, '2030-01-31', 'Listrik akhir bulan', 'cash', null);
        $this->seedOperationalExpense('expense-4', 'expense-category-1', 50000, '2030-01-07', 'Deleted row', 'cash', '2030-01-07 10:00:00');
        $this->seedOperationalExpense('expense-5', 'expense-category-2', 90000, '2030-02-01', 'Bulan berikutnya', 'cash', null);

        $daily = $this->reportTotals('2030-01-07', '2030-01-07');
        $weekly = $this->reportTotals('2030-01-06', '2030-01-12');
        $monthly = $this->reportTotals('2030-01-01', '2030-01-31');
        $custom = $this->reportTotals('2030-01-01', '2030-01-31');

        $this->assertSame([
            'total_rows' => 1,
            'total_amount_rupiah' => 25000,
        ], $daily);

        $this->assertSame([
            'total_rows' => 2,
            'total_amount_rupiah' => 125000,
        ], $weekly);

        $this->assertSame([
            'total_rows' => 3,
            'total_amount_rupiah' => 200000,
        ], $monthly);

        $this->assertSame($monthly, $custom);
    }

    public function test_operational_expense_summary_changes_immediately_after_record_and_soft_delete(): void
    {
        $this->seedExpenseCategory('expense-category-1', 'LISTRIK', 'Listrik', true);

        $record = app(RecordOperationalExpenseHandler::class)->handle(
            'expense-category-1',
            33000,
            '2030-02-10',
            'Beli air galon',
            'tf',
        );

        $this->assertTrue($record->isSuccess());

        $expense = $record->data()['expense'] ?? null;
        $this->assertIsArray($expense);
        $this->assertSame(33000, $expense['amount_rupiah'] ?? null);

        $afterCreate = $this->reportTotals('2030-02-10', '2030-02-10');
        $this->assertSame([
            'total_rows' => 1,
            'total_amount_rupiah' => 33000,
        ], $afterCreate);

        $delete = app(SoftDeleteOperationalExpenseHandler::class)->handle(
            (string) $expense['id'],
            'actor-admin-1',
        );

        $this->assertTrue($delete->isSuccess());

        $afterDelete = $this->reportTotals('2030-02-10', '2030-02-10');
        $this->assertSame([
            'total_rows' => 0,
            'total_amount_rupiah' => 0,
        ], $afterDelete);
    }

    private function reportTotals(string $from, string $to): array
    {
        $result = app(GetOperationalExpenseSummaryHandler::class)->handle($from, $to);
        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);

        $rows = $data['rows'] ?? [];
        $this->assertIsArray($rows);

        return [
            'total_rows' => count($rows),
            'total_amount_rupiah' => array_sum(array_column($rows, 'amount_rupiah')),
        ];
    }

    private function seedExpenseCategory(
        string $id,
        string $code,
        string $name,
        bool $isActive,
    ): void {
        DB::table('expense_categories')->insert([
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'description' => null,
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedOperationalExpense(
        string $id,
        string $categoryId,
        int $amountRupiah,
        string $expenseDate,
        string $description,
        string $paymentMethod,
        ?string $deletedAt,
    ): void {
        DB::table('operational_expenses')->insert([
            'id' => $id,
            'category_id' => $categoryId,
            'category_code_snapshot' => 'SNAP',
            'category_name_snapshot' => 'Snapshot',
            'amount_rupiah' => $amountRupiah,
            'expense_date' => $expenseDate,
            'description' => $description,
            'payment_method' => $paymentMethod,
            'reference_no' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => $deletedAt,
        ]);
    }
}
