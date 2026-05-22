<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryWriterAdapter;
use App\Application\Expense\UseCases\ActivateExpenseCategoryHandler;
use App\Ports\Out\AuditEventWriterPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ActivateExpenseCategoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_activate_expense_category_updates_row_and_records_canonical_audit(): void
    {
        $this->seedCategory('cat-1', false);

        $handler = new ActivateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            app(AuditEventWriterPort::class),
            app(ClockPort::class),
            app(UuidPort::class),
        );

        $result = $handler->handle('cat-1', 'admin-1');

        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('expense_categories', ['id' => 'cat-1', 'is_active' => 1]);

        $event = DB::table('audit_events')->where('event_name', 'expense_category_activated')->first();

        $this->assertNotNull($event);
        $this->assertSame('expense', $event->bounded_context);
        $this->assertSame('expense_category', $event->aggregate_type);
        $this->assertSame('cat-1', $event->aggregate_id);
        $this->assertSame('admin-1', $event->actor_id);
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
