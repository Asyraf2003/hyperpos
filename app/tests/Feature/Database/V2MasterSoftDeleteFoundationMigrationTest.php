<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class V2MasterSoftDeleteFoundationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_and_suppliers_have_soft_delete_foundation_columns(): void
    {
        foreach (['deleted_at', 'deleted_by_actor_id', 'delete_reason'] as $column) {
            self::assertTrue(Schema::hasColumn('products', $column), "Missing products.{$column}");
            self::assertTrue(Schema::hasColumn('suppliers', $column), "Missing suppliers.{$column}");
        }
    }

    public function test_products_and_suppliers_have_deleted_at_indexes(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertIndexColumns('products', 'products_deleted_at_idx', ['deleted_at']);
        $this->assertIndexColumns('suppliers', 'suppliers_deleted_at_idx', ['deleted_at']);
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
