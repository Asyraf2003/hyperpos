<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Adapters\Out\Audit\DatabaseAuditLogAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryWriterAdapter;
use App\Application\Expense\UseCases\DeactivateExpenseCategoryHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DeactivateExpenseCategoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivate_expense_category_updates_row_and_records_audit(): void
    {
        $this->seedCategory('cat-1', true);

        $handler = new DeactivateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            new DatabaseAuditLogAdapter(),
        );

        $result = $handler->handle('cat-1', 'admin-1');

        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('expense_categories', ['id' => 'cat-1', 'is_active' => 0]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'expense_category_deactivated']);
    }

    private function seedCategory(string $id, bool $isActive): void
    {
        DB::table('expense_categories')->insert([
            'id' => $id,
            'code' => 'EXP-ELEC',
            'name' => 'Listrik',
            'description' => null,
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
