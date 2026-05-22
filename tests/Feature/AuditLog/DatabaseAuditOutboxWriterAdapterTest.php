<?php

declare(strict_types=1);

namespace Tests\Feature\AuditLog;

use App\Adapters\Out\Audit\DatabaseAuditOutboxWriterAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Feature\AuditLog\Support\AuditOutboxTestEventFactory;
use Tests\TestCase;

final class DatabaseAuditOutboxWriterAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_persists_pending_outbox_row_from_audit_event_write(): void
    {
        $this->app->make(DatabaseAuditOutboxWriterAdapter::class)
            ->write(AuditOutboxTestEventFactory::event('audit-outbox-event-001'));

        $row = DB::table('audit_outbox')->where('audit_event_id', 'audit-outbox-event-001')->first();

        $this->assertNotNull($row);
        $this->assertSame('expense', $row->bounded_context);
        $this->assertSame('expense_category', $row->aggregate_type);
        $this->assertSame('cat-1', $row->aggregate_id);
        $this->assertSame('expense_category_updated', $row->event_name);
        $this->assertSame('admin-1', $row->actor_id);
        $this->assertSame('pending', $row->status);
        $this->assertSame(0, (int) $row->attempts);

        $metadata = json_decode((string) $row->metadata_json, true, 512, JSON_THROW_ON_ERROR);
        $snapshots = json_decode((string) $row->snapshots_json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('cat-1', $metadata['category_id']);
        $this->assertCount(2, $snapshots);
        $this->assertSame('before', $snapshots[0]['snapshot_kind']);
        $this->assertSame('after', $snapshots[1]['snapshot_kind']);
    }

    public function test_writer_does_not_materialize_canonical_audit_tables(): void
    {
        $this->app->make(DatabaseAuditOutboxWriterAdapter::class)
            ->write(AuditOutboxTestEventFactory::event('audit-outbox-event-002'));

        $this->assertDatabaseCount('audit_outbox', 1);
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertDatabaseCount('audit_event_snapshots', 0);
    }

    public function test_writer_participates_in_outer_database_transaction(): void
    {
        DB::beginTransaction();

        try {
            $this->app->make(DatabaseAuditOutboxWriterAdapter::class)
                ->write(AuditOutboxTestEventFactory::event('audit-outbox-event-003'));

            throw new RuntimeException('force rollback after audit outbox write');
        } catch (RuntimeException) {
            DB::rollBack();
        }

        $this->assertDatabaseMissing('audit_outbox', [
            'audit_event_id' => 'audit-outbox-event-003',
        ]);
    }
}
