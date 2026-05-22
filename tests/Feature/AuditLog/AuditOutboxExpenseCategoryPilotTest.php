<?php

declare(strict_types=1);

namespace Tests\Feature\AuditLog;

use App\Adapters\Out\Audit\DatabaseAuditOutboxWriterAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryWriterAdapter;
use App\Application\Expense\UseCases\UpdateExpenseCategoryHandler;
use App\Ports\Out\AuditEventWriterPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AuditOutboxExpenseCategoryPilotTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_category_update_can_stage_audit_in_outbox_then_materialize(): void
    {
        $this->app->bind(AuditEventWriterPort::class, DatabaseAuditOutboxWriterAdapter::class);
        $this->seedCategory('cat-1', 'EXP-ELEC', 'Listrik', true, 'Lama');

        $handler = new UpdateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            app(AuditEventWriterPort::class),
            app(ClockPort::class),
            app(UuidPort::class),
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

        $this->assertDatabaseHas('audit_outbox', [
            'bounded_context' => 'expense',
            'aggregate_type' => 'expense_category',
            'aggregate_id' => 'cat-1',
            'event_name' => 'expense_category_updated',
            'actor_id' => 'admin-1',
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertDatabaseCount('audit_event_snapshots', 0);

        $this->artisan('audit:outbox:process', ['--limit' => 10])->assertExitCode(0);

        $event = DB::table('audit_events')
            ->where('event_name', 'expense_category_updated')
            ->first();

        $this->assertNotNull($event);
        $this->assertSame('cat-1', $event->aggregate_id);
        $this->assertSame('admin-1', $event->actor_id);

        $this->assertDatabaseHas('audit_event_snapshots', [
            'audit_event_id' => $event->id,
            'snapshot_kind' => 'before',
        ]);
        $this->assertDatabaseHas('audit_event_snapshots', [
            'audit_event_id' => $event->id,
            'snapshot_kind' => 'after',
        ]);
        $this->assertDatabaseHas('audit_outbox', [
            'audit_event_id' => $event->id,
            'status' => 'processed',
        ]);
    }

    private function seedCategory(
        string $id,
        string $code,
        string $name,
        bool $isActive,
        ?string $description
    ): void {
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
