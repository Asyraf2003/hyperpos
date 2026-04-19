<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class V2AuditFoundationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_tables_exist_with_expected_columns(): void
    {
        self::assertTrue(Schema::hasTable('audit_events'));
        self::assertTrue(Schema::hasTable('audit_event_snapshots'));

        foreach ([
            'id',
            'bounded_context',
            'aggregate_type',
            'aggregate_id',
            'event_name',
            'actor_id',
            'actor_role',
            'reason',
            'source_channel',
            'request_id',
            'correlation_id',
            'occurred_at',
            'metadata_json',
        ] as $column) {
            self::assertTrue(Schema::hasColumn('audit_events', $column), "Missing audit_events.{$column}");
        }

        foreach ([
            'id',
            'audit_event_id',
            'snapshot_kind',
            'payload_json',
            'created_at',
        ] as $column) {
            self::assertTrue(Schema::hasColumn('audit_event_snapshots', $column), "Missing audit_event_snapshots.{$column}");
        }
    }

    public function test_audit_indexes_and_foreign_key_exist(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertIndexColumns('audit_events', 'audit_events_event_name_idx', ['event_name']);
        $this->assertIndexColumns('audit_events', 'audit_events_occurred_at_idx', ['occurred_at']);
        $this->assertIndexColumns('audit_events', 'audit_events_context_occurred_idx', ['bounded_context', 'occurred_at']);
        $this->assertIndexColumns('audit_events', 'audit_events_aggregate_lookup_idx', ['aggregate_type', 'aggregate_id', 'occurred_at']);
        $this->assertIndexColumns('audit_events', 'audit_events_actor_lookup_idx', ['actor_id', 'occurred_at']);
        $this->assertIndexColumns('audit_event_snapshots', 'audit_event_snapshots_event_kind_unique', ['audit_event_id', 'snapshot_kind']);

        $databaseName = (string) DB::connection()->getDatabaseName();

        $row = DB::selectOne(
            'SELECT
                k.CONSTRAINT_NAME,
                k.TABLE_NAME,
                k.COLUMN_NAME,
                k.REFERENCED_TABLE_NAME,
                k.REFERENCED_COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE k
             WHERE k.TABLE_SCHEMA = ?
               AND k.TABLE_NAME = ?
               AND k.COLUMN_NAME = ?
               AND k.CONSTRAINT_NAME = ?
               AND k.REFERENCED_TABLE_NAME = ?
               AND k.REFERENCED_COLUMN_NAME = ?
             LIMIT 1',
            [
                $databaseName,
                'audit_event_snapshots',
                'audit_event_id',
                'fk_audit_event_snapshots_event',
                'audit_events',
                'id',
            ]
        );

        self::assertNotNull($row, 'Foreign key fk_audit_event_snapshots_event not found.');
    }

    private function skipUnlessMysqlOrMariaDb(): void
    {
        $driver = DB::getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('MySQL/MariaDB metadata assertions only.');
        }
    }

    private function assertIndexColumns(string $table, string $indexName, array $expectedColumns): void
    {
        $rows = collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->filter(fn (object $row): bool => (string) $row->Key_name === $indexName)
            ->sortBy(fn (object $row): int => (int) $row->Seq_in_index)
            ->values();

        self::assertNotEmpty($rows->all(), "Index {$indexName} not found on {$table}.");

        $actualColumns = $rows
            ->map(fn (object $row): string => (string) $row->Column_name)
            ->all();

        self::assertSame($expectedColumns, $actualColumns, "Unexpected columns for {$indexName} on {$table}.");
    }
}
