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

        $handler = new DeactivateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            app(AuditEventWriterPort::class),
            app(ClockPort::class),
            app(UuidPort::class),
        );

        $result = $handler->handle('cat-1', 'admin-1');

        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('expense_categories', ['id' => 'cat-1', 'is_active' => 0]);

        $event = DB::table('audit_events')->where('event_name', 'expense_category_deactivated')->first();

        $this->assertNotNull($event);
        $this->assertSame('expense', $event->bounded_context);
        $this->assertSame('expense_category', $event->aggregate_type);
        $this->assertSame('cat-1', $event->aggregate_id);
        $this->assertSame('admin-1', $event->actor_id);

        $metadata = json_decode((string) $event->metadata_json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('cat-1', $metadata['category_id']);
        $this->assertSame('admin-1', $metadata['performed_by_actor_id']);

        $snapshots = DB::table('audit_event_snapshots')
            ->where('audit_event_id', $event->id)
            ->pluck('payload_json', 'snapshot_kind')
            ->all();

        $this->assertArrayHasKey('before', $snapshots);
        $this->assertArrayHasKey('after', $snapshots);

        $before = json_decode((string) $snapshots['before'], true, 512, JSON_THROW_ON_ERROR);
        $after = json_decode((string) $snapshots['after'], true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($before['is_active']);
        $this->assertFalse($after['is_active']);

        $this->assertDatabaseCount('audit_logs', 0);
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
