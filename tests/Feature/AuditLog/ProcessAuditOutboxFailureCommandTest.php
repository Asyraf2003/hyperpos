<?php

declare(strict_types=1);

namespace Tests\Feature\AuditLog;

use App\Adapters\Out\Audit\DatabaseAuditOutboxWriterAdapter;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\AuditLog\Support\AuditOutboxTestEventFactory;
use Tests\TestCase;

final class ProcessAuditOutboxFailureCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_marks_failed_row_when_materialization_fails(): void
    {
        $this->app->make(DatabaseAuditOutboxWriterAdapter::class)
            ->write(AuditOutboxTestEventFactory::event('audit-outbox-process-fail-001'));

        DB::table('audit_events')->insert([
            'id' => 'audit-outbox-process-fail-001',
            'bounded_context' => 'expense',
            'aggregate_type' => 'expense_category',
            'aggregate_id' => 'cat-1',
            'event_name' => 'expense_category_updated',
            'actor_id' => 'admin-1',
            'actor_role' => null,
            'reason' => null,
            'source_channel' => 'web_admin',
            'request_id' => null,
            'correlation_id' => null,
            'occurred_at' => new DateTimeImmutable('2026-05-23 10:00:00'),
            'metadata_json' => null,
        ]);

        $this->artisan('audit:outbox:process', [
            '--limit' => 10,
            '--max-attempts' => 1,
        ])->assertExitCode(1);

        $row = DB::table('audit_outbox')
            ->where('audit_event_id', 'audit-outbox-process-fail-001')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('failed', $row->status);
        $this->assertSame(1, (int) $row->attempts);
        $this->assertNotNull($row->last_error);
        $this->assertNull($row->locked_at);

        $this->assertSame(1, DB::table('audit_events')
            ->where('id', 'audit-outbox-process-fail-001')
            ->count());
    }
}
