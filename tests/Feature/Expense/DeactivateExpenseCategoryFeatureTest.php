<?php
declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryWriterAdapter;
use App\Application\Expense\UseCases\DeactivateExpenseCategoryHandler;
use App\Ports\Out\AuditEventWriterPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DeactivateExpenseCategoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivate_expense_category_updates_row_and_records_canonical_audit(): void
    {
        $this->seedCategory('cat-1', true);
        $result = $this->handler()->handle('cat-1', 'admin-1');

        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('expense_categories', ['id' => 'cat-1', 'is_active' => 0]);
        $outbox = DB::table('audit_outbox')->where('event_name', 'expense_category_deactivated')->first();
        self::assertNotNull($outbox);
        self::assertSame('pending', $outbox->status);
        self::assertSame('cat-1', $outbox->aggregate_id);
        self::assertSame('admin-1', $outbox->actor_id);
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertDatabaseCount('audit_event_snapshots', 0);

        $this->artisan('audit:outbox:process', ['--limit' => 10])->assertExitCode(0);

        $this->assertDatabaseHas('audit_events', [
            'id' => $outbox->audit_event_id,
            'bounded_context' => 'expense',
            'aggregate_type' => 'expense_category',
            'aggregate_id' => 'cat-1',
            'event_name' => 'expense_category_deactivated',
            'actor_id' => 'admin-1',
        ]);
        foreach (['before', 'after'] as $kind) {
            $this->assertDatabaseHas('audit_event_snapshots', [
                'audit_event_id' => $outbox->audit_event_id,
                'snapshot_kind' => $kind,
            ]);
        }
        $this->assertDatabaseHas('audit_outbox', [
            'audit_event_id' => $outbox->audit_event_id,
            'status' => 'processed',
        ]);
    }

    private function handler(): DeactivateExpenseCategoryHandler
    {
        return new DeactivateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            app(AuditEventWriterPort::class),
            app(ClockPort::class),
            app(UuidPort::class),
        );
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
