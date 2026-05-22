<?php

declare(strict_types=1);

namespace Tests\Feature\AuditLog;

use App\Adapters\Out\Audit\DatabaseAuditOutboxWriterAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\AuditLog\Support\AuditOutboxTestEventFactory;
use Tests\TestCase;

final class ProcessAuditOutboxDuplicateRunCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_second_run_does_not_duplicate_processed_canonical_audit(): void
    {
        $this->app->make(DatabaseAuditOutboxWriterAdapter::class)
            ->write(AuditOutboxTestEventFactory::event('audit-outbox-process-duplicate-001'));

        $this->artisan('audit:outbox:process', ['--limit' => 10])->assertExitCode(0);
        $this->artisan('audit:outbox:process', ['--limit' => 10])->assertExitCode(0);

        $this->assertSame(1, DB::table('audit_events')
            ->where('id', 'audit-outbox-process-duplicate-001')
            ->count());

        $this->assertSame(2, DB::table('audit_event_snapshots')
            ->where('audit_event_id', 'audit-outbox-process-duplicate-001')
            ->count());

        $this->assertDatabaseHas('audit_outbox', [
            'audit_event_id' => 'audit-outbox-process-duplicate-001',
            'status' => 'processed',
        ]);
    }
}
