<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class V2ProductSearchNormalizationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_have_normalized_search_columns(): void
    {
        self::assertTrue(Schema::hasColumn('products', 'nama_barang_normalized'));
        self::assertTrue(Schema::hasColumn('products', 'merek_normalized'));
    }

    public function test_products_have_normalized_search_indexes_and_uniques(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertIndexColumns(
            'products',
            'products_nama_barang_normalized_idx',
            ['nama_barang_normalized']
        );

        $this->assertIndexColumns(
            'products',
            'products_merek_normalized_idx',
            ['merek_normalized']
        );

        $this->assertIndexColumns(
            'products',
            'products_kode_barang_unique',
            ['kode_barang', 'active_unique_marker']
        );

        $this->assertIndexColumns(
            'products',
            'products_business_identity_unique',
            ['nama_barang_normalized', 'merek_normalized', 'ukuran', 'active_unique_marker']
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
}
