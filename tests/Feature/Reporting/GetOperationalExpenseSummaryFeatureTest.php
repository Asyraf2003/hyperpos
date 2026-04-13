<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetOperationalExpenseSummaryHandler;
use App\Application\Shared\DTO\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetOperationalExpenseSummaryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_operational_expense_summary_handler_returns_active_expenses_only_and_passes_reconciliation(): void
    {
        $this->seedExpenseCategory('expense-category-1', 'LISTRIK', 'Listrik', true);
        $this->seedExpenseCategory('expense-category-2', 'MAKAN', 'Makan', true);

        $this->seedOperationalExpense(
            'expense-1',
            'expense-category-1',
            150000,
            '2026-03-15',
            'Bayar listrik workshop',
            'cash',
            'INV-001',
            null,
        );

        $this->seedOperationalExpense(
            'expense-2',
            'expense-category-1',
            100000,
            '2026-03-15',
            'Biaya listrik dihapus',
            'cash',
            'INV-002',
            '2026-03-15 12:00:00',
        );

        $this->seedOperationalExpense(
            'expense-3',
            'expense-category-2',
            20000,
            '2026-03-15',
            'Biaya makan dihapus',
            'cash',
            'INV-003',
            '2026-03-15 13:00:00',
        );

        $this->seedOperationalExpense(
            'expense-4',
            'expense-category-2',
            25000,
            '2026-03-16',
            'Makan lembur tim',
            'transfer',
            null,
            null,
        );

        $this->seedOperationalExpense(
            'expense-5',
            'expense-category-1',
            9999,
            '2026-03-18',
            'Di luar scope tanggal',
            'cash',
            'INV-999',
            null,
        );

        $result = app(GetOperationalExpenseSummaryHandler::class)
            ->handle('2026-03-15', '2026-03-16');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());

        $data = $result->data();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('rows', $data);

        $this->assertSame([
            [
                'expense_id' => 'expense-1',
                'expense_date' => '2026-03-15',
                'category_id' => 'expense-category-1',
                'category_code' => 'LISTRIK',
                'category_name' => 'Listrik',
                'amount_rupiah' => 150000,
                'description' => 'Bayar listrik workshop',
                'payment_method' => 'cash',
                'reference_no' => 'INV-001',
            ],
            [
                'expense_id' => 'expense-4',
                'expense_date' => '2026-03-16',
                'category_id' => 'expense-category-2',
                'category_code' => 'MAKAN',
                'category_name' => 'Makan',
                'amount_rupiah' => 25000,
                'description' => 'Makan lembur tim',
                'payment_method' => 'transfer',
                'reference_no' => null,
            ],
        ], $data['rows']);
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
        ?string $referenceNo,
        ?string $deletedAt,
    ): void {
        DB::table('operational_expenses')->insert([
            'id' => $id,
            'category_id' => $categoryId,
            'amount_rupiah' => $amountRupiah,
            'expense_date' => $expenseDate,
            'description' => $description,
            'payment_method' => $paymentMethod,
            'reference_no' => $referenceNo,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => $deletedAt,
        ]);
    }
}
