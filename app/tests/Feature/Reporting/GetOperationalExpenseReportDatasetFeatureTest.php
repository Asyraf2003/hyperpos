<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetOperationalExpenseReportDatasetHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetOperationalExpenseReportDatasetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_expense_report_dataset_returns_single_exact_dataset_from_summary_rows(): void
    {
        $this->seedExpenseCategory('expense-category-1', 'LISTRIK', 'Listrik', true);
        $this->seedExpenseCategory('expense-category-2', 'MAKAN', 'Makan', true);

        $this->seedOperationalExpense('expense-1', 'expense-category-1', 100000, '2030-01-06', 'Bayar listrik', 'cash', null);
        $this->seedOperationalExpense('expense-2', 'expense-category-2', 25000, '2030-01-07', 'Makan tim', 'tf', null);
        $this->seedOperationalExpense('expense-3', 'expense-category-2', 15000, '2030-01-07', 'Snack tim', 'cash', null);
        $this->seedOperationalExpense('expense-4', 'expense-category-1', 75000, '2030-01-31', 'Listrik akhir bulan', 'cash', null);
        $this->seedOperationalExpense('expense-5', 'expense-category-1', 50000, '2030-01-07', 'Deleted row', 'cash', '2030-01-07 10:00:00');
        $this->seedOperationalExpense('expense-6', 'expense-category-2', 90000, '2030-02-01', 'Bulan berikutnya', 'cash', null);

        $result = app(GetOperationalExpenseReportDatasetHandler::class)
            ->handle('2030-01-01', '2030-01-31');

        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);

        $rows = $data['rows'] ?? null;
        $summary = $data['summary'] ?? null;
        $periodRows = $data['period_rows'] ?? null;
        $categoryRows = $data['category_rows'] ?? null;

        $this->assertIsArray($rows);
        $this->assertIsArray($summary);
        $this->assertIsArray($periodRows);
        $this->assertIsArray($categoryRows);

        $this->assertCount(4, $rows);

        $this->assertSame([
            'total_rows' => 4,
            'total_amount_rupiah' => 215000,
            'top_category_name' => 'Listrik',
            'top_category_amount_rupiah' => 175000,
            'average_daily_rupiah' => 6935,
        ], $summary);

        $this->assertSame([
            [
                'period_label' => '2030-01-06',
                'total_rows' => 1,
                'total_amount_rupiah' => 100000,
            ],
            [
                'period_label' => '2030-01-07',
                'total_rows' => 2,
                'total_amount_rupiah' => 40000,
            ],
            [
                'period_label' => '2030-01-31',
                'total_rows' => 1,
                'total_amount_rupiah' => 75000,
            ],
        ], $periodRows);

        $this->assertSame([
            [
                'category_id' => 'expense-category-1',
                'category_code' => 'LISTRIK',
                'category_name' => 'Listrik',
                'total_rows' => 2,
                'total_amount_rupiah' => 175000,
            ],
            [
                'category_id' => 'expense-category-2',
                'category_code' => 'MAKAN',
                'category_name' => 'Makan',
                'total_rows' => 2,
                'total_amount_rupiah' => 40000,
            ],
        ], $categoryRows);

        $this->assertSame(
            $summary['total_amount_rupiah'],
            array_sum(array_column($rows, 'amount_rupiah'))
        );

        $this->assertSame(
            $summary['total_amount_rupiah'],
            array_sum(array_column($periodRows, 'total_amount_rupiah'))
        );

        $this->assertSame(
            $summary['total_amount_rupiah'],
            array_sum(array_column($categoryRows, 'total_amount_rupiah'))
        );

        $this->assertSame(
            6935,
            intdiv($summary['total_amount_rupiah'], 31)
        );
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
