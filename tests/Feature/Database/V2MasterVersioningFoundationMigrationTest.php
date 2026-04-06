<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class V2MasterVersioningFoundationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_and_supplier_version_tables_exist_with_expected_columns(): void
    {
        self::assertTrue(Schema::hasTable('product_versions'));
        self::assertTrue(Schema::hasTable('supplier_versions'));

        foreach ([
            'id',
            'product_id',
            'revision_no',
            'event_name',
            'changed_by_actor_id',
            'change_reason',
            'changed_at',
            'snapshot_json',
        ] as $column) {
            self::assertTrue(Schema::hasColumn('product_versions', $column), "Missing product_versions.{$column}");
        }

        foreach ([
            'id',
            'supplier_id',
            'revision_no',
            'event_name',
            'changed_by_actor_id',
            'change_reason',
            'changed_at',
            'snapshot_json',
        ] as $column) {
            self::assertTrue(Schema::hasColumn('supplier_versions', $column), "Missing supplier_versions.{$column}");
        }
    }

    public function test_product_and_supplier_version_indexes_and_foreign_keys_exist(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertIndexColumns(
            'product_versions',
            'product_versions_product_revision_unique',
            ['product_id', 'revision_no']
        );
        $this->assertIndexColumns(
            'product_versions',
            'product_versions_product_changed_at_idx',
            ['product_id', 'changed_at']
        );
        $this->assertIndexColumns(
            'product_versions',
            'product_versions_event_name_idx',
            ['event_name']
        );

        $this->assertIndexColumns(
            'supplier_versions',
            'supplier_versions_supplier_revision_unique',
            ['supplier_id', 'revision_no']
        );
        $this->assertIndexColumns(
            'supplier_versions',
            'supplier_versions_supplier_changed_at_idx',
            ['supplier_id', 'changed_at']
        );
        $this->assertIndexColumns(
            'supplier_versions',
            'supplier_versions_event_name_idx',
            ['event_name']
        );

        $this->assertForeignKey(
            'product_versions',
            'product_id',
            'fk_product_versions_product',
            'products',
            'id'
        );

        $this->assertForeignKey(
            'supplier_versions',
            'supplier_id',
            'fk_supplier_versions_supplier',
            'suppliers',
            'id'
        );
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

    private function assertForeignKey(
        string $table,
        string $column,
        string $constraintName,
        string $referencedTable,
        string $referencedColumn
    ): void {
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
                $table,
                $column,
                $constraintName,
                $referencedTable,
                $referencedColumn,
            ]
        );

        self::assertNotNull(
            $row,
            "Foreign key {$constraintName} not found on {$table}.{$column} -> {$referencedTable}.{$referencedColumn}."
        );
    }
}
