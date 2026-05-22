<?php

declare(strict_types=1);

namespace Tests\Feature\AuditLog;

use App\Adapters\Out\Audit\DatabaseAuditOutboxWriterAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\AuditLog\Support\AuditOutboxTestEventFactory;
use Tests\TestCase;

final class ProcessAuditOutboxMaterializationCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_materializes_pending_outbox_row_to_canonical_audit_tables(): void
    {
        $this->app->make(DatabaseAuditOutboxWriterAdapter::class)
            ->write(AuditOutboxTestEventFactory::event('audit-outbox-process-001'));

        $this->artisan('audit:outbox:process', ['--limit' => 10])->assertExitCode(0);

        $this->assertDatabaseHas('audit_events', [
            'id' => 'audit-outbox-process-001',
            'bounded_context' => 'expense',
            'aggregate_type' => 'expense_category',
            'aggregate_id' => 'cat-1',
            'event_name' => 'expense_category_updated',
            'actor_id' => 'admin-1',
        ]);

        $this->assertDatabaseHas('audit_event_snapshots', [
            'audit_event_id' => 'audit-outbox-process-001',
            'snapshot_kind' => 'before',
        ]);

        $this->assertDatabaseHas('audit_event_snapshots', [
            'audit_event_id' => 'audit-outbox-process-001',
            'snapshot_kind' => 'after',
        ]);

        $row = DB::table('audit_outbox')
            ->where('audit_event_id', 'audit-outbox-process-001')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('processed', $row->status);
        $this->assertSame(0, (int) $row->attempts);
        $this->assertNull($row->locked_at);
        $this->assertNotNull($row->processed_at);
    }
}
