<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Adapters\Out\Audit\DatabaseAuditLogAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryWriterAdapter;
use App\Application\Expense\UseCases\UpdateExpenseCategoryHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UpdateExpenseCategoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_expense_category_updates_row_and_records_audit(): void
    {
        $this->seedCategory('cat-1', 'EXP-ELEC', 'Listrik', true, 'Lama');

        $handler = new UpdateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            new DatabaseAuditLogAdapter(),
        );

        $result = $handler->handle('cat-1', 'EXP-UTIL', 'Utilitas', 'Baru', 'admin-1');

        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('expense_categories', [
            'id' => 'cat-1',
            'code' => 'EXP-UTIL',
            'name' => 'Utilitas',
            'description' => 'Baru',
            'is_active' => 1,
        ]);

        $log = DB::table('audit_logs')->where('event', 'expense_category_updated')->first();
        $this->assertNotNull($log);
        $context = json_decode((string) $log->context, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('cat-1', $context['category_id']);
        $this->assertSame('admin-1', $context['performed_by_actor_id']);
        $this->assertSame('EXP-ELEC', $context['before']['code']);
        $this->assertSame('EXP-UTIL', $context['after']['code']);
    }

    public function test_update_expense_category_rejects_duplicate_code(): void
    {
        $this->seedCategory('cat-1', 'EXP-ELEC', 'Listrik', true, null);
        $this->seedCategory('cat-2', 'EXP-WIFI', 'Wifi', true, null);

        $handler = new UpdateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            new DatabaseAuditLogAdapter(),
        );

        $result = $handler->handle('cat-1', 'EXP-WIFI', 'Utilitas', null, 'admin-1');

        $this->assertTrue($result->isFailure());
        $this->assertSame(['expense_category' => ['EXPENSE_CATEGORY_CODE_ALREADY_EXISTS']], $result->errors());
        $this->assertDatabaseCount('audit_logs', 0);
    }

    private function seedCategory(string $id, string $code, string $name, bool $isActive, ?string $description): void
    {
        DB::table('expense_categories')->insert([
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
