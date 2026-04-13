<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseOperationalExpenseWriterAdapter;
use App\Application\Expense\UseCases\RecordOperationalExpenseHandler;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RecordOperationalExpenseFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_operational_expense_handler_stores_active_expense(): void
    {
        $this->seedCategory('expense-category-1', 'LISTRIK', 'Listrik', true);

        $handler = new RecordOperationalExpenseHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseOperationalExpenseWriterAdapter(),
            new class () implements UuidPort {
                public function generate(): string { return 'expense-1'; }
            },
        );

        $result = $handler->handle(
            'expense-category-1',
            250000,
            '2026-03-17',
            'Bayar token listrik workshop',
            'cash',
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());

        $this->assertDatabaseHas('operational_expenses', [
            'id' => 'expense-1',
            'category_id' => 'expense-category-1',
            'category_code_snapshot' => 'LISTRIK',
            'category_name_snapshot' => 'Listrik',
            'amount_rupiah' => 250000,
            'expense_date' => '2026-03-17',
            'description' => 'Bayar token listrik workshop',
            'payment_method' => 'cash',
            'status' => 'posted',
            'deleted_at' => null,
        ]);
    }

    public function test_record_operational_expense_handler_rejects_missing_category(): void
    {
        $handler = new RecordOperationalExpenseHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseOperationalExpenseWriterAdapter(),
            new class () implements UuidPort {
                public function generate(): string { return 'expense-1'; }
            },
        );

        $result = $handler->handle('missing-category', 250000, '2026-03-17', 'Bayar token listrik workshop', 'cash');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isFailure());
        $this->assertSame(['expense' => ['EXPENSE_CATEGORY_NOT_FOUND']], $result->errors());
        $this->assertDatabaseCount('operational_expenses', 0);
    }

    public function test_record_operational_expense_handler_rejects_inactive_category(): void
    {
        $this->seedCategory('expense-category-1', 'LISTRIK', 'Listrik', false);

        $handler = new RecordOperationalExpenseHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseOperationalExpenseWriterAdapter(),
            new class () implements UuidPort {
                public function generate(): string { return 'expense-1'; }
            },
        );

        $result = $handler->handle('expense-category-1', 250000, '2026-03-17', 'Bayar token listrik workshop', 'cash');

        $this->assertTrue($result->isFailure());
        $this->assertSame(['expense' => ['EXPENSE_CATEGORY_INACTIVE']], $result->errors());
        $this->assertDatabaseCount('operational_expenses', 0);
    }

    public function test_record_operational_expense_handler_rejects_zero_amount(): void
    {
        $this->seedCategory('expense-category-1', 'LISTRIK', 'Listrik', true);

        $handler = new RecordOperationalExpenseHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseOperationalExpenseWriterAdapter(),
            new class () implements UuidPort {
                public function generate(): string { return 'expense-1'; }
            },
        );

        $result = $handler->handle('expense-category-1', 0, '2026-03-17', 'Bayar token listrik workshop', 'cash');

        $this->assertTrue($result->isFailure());
        $this->assertSame(['expense' => ['INVALID_OPERATIONAL_EXPENSE']], $result->errors());
        $this->assertDatabaseCount('operational_expenses', 0);
    }

    public function test_record_operational_expense_handler_rejects_invalid_expense_date(): void
    {
        $this->seedCategory('expense-category-1', 'LISTRIK', 'Listrik', true);

        $handler = new RecordOperationalExpenseHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseOperationalExpenseWriterAdapter(),
            new class () implements UuidPort {
                public function generate(): string { return 'expense-1'; }
            },
        );

        $result = $handler->handle('expense-category-1', 250000, '17-03-2026', 'Bayar token listrik workshop', 'cash');

        $this->assertTrue($result->isFailure());
        $this->assertSame(['expense' => ['INVALID_OPERATIONAL_EXPENSE']], $result->errors());
        $this->assertDatabaseCount('operational_expenses', 0);
    }

    private function seedCategory(string $id, string $code, string $name, bool $isActive): void
    {
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
}
