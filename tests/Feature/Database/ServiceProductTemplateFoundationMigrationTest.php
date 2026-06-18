<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ServiceProductTemplateFoundationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_product_templates_table_exists_with_expected_columns(): void
    {
        self::assertTrue(Schema::hasTable('service_product_templates'));

        foreach ([
            'id',
            'product_id',
            'service_catalog_item_id',
            'default_service_price_rupiah',
            'default_package_total_rupiah',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ] as $column) {
            self::assertTrue(
                Schema::hasColumn('service_product_templates', $column),
                "Missing service_product_templates.{$column}"
            );
        }
    }

    public function test_service_product_templates_indexes_and_foreign_keys_exist(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertIndexColumns(
            'service_product_templates',
            'service_product_templates_product_idx',
            ['product_id']
        );

        $this->assertIndexColumns(
            'service_product_templates',
            'service_product_templates_service_catalog_item_idx',
            ['service_catalog_item_id']
        );

        $this->assertIndexColumns(
            'service_product_templates',
            'service_product_templates_active_idx',
            ['is_active']
        );

        $this->assertIndexColumns(
            'service_product_templates',
            'service_product_templates_active_lookup_idx',
            ['product_id', 'is_active', 'sort_order']
        );

        $this->assertForeignKey(
            'service_product_templates',
            'product_id',
            'fk_service_product_templates_product',
            'products',
            'id'
        );

        $this->assertForeignKey(
            'service_product_templates',
            'service_catalog_item_id',
            'fk_service_product_templates_service_catalog_item',
            'service_catalog_items',
            'id'
        );
    }

    public function test_service_product_templates_accept_valid_template_and_reject_non_positive_defaults(): void
    {
        $this->insertProductAndServiceCatalogItem();

        DB::table('service_product_templates')->insert([
            'id' => 'service-product-template-valid-1',
            'product_id' => 'product-template-foundation-1',
            'service_catalog_item_id' => 'service-template-foundation-1',
            'default_service_price_rupiah' => 50000,
            'default_package_total_rupiah' => 150000,
            'is_active' => true,
            'sort_order' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('service_product_templates', [
            'id' => 'service-product-template-valid-1',
            'product_id' => 'product-template-foundation-1',
            'service_catalog_item_id' => 'service-template-foundation-1',
            'default_service_price_rupiah' => 50000,
            'default_package_total_rupiah' => 150000,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $this->assertInsertFails([
            'id' => 'service-product-template-invalid-service-price',
            'product_id' => 'product-template-foundation-1',
            'service_catalog_item_id' => 'service-template-foundation-1',
            'default_service_price_rupiah' => 0,
            'default_package_total_rupiah' => 150000,
            'is_active' => true,
            'sort_order' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertInsertFails([
            'id' => 'service-product-template-invalid-package-total',
            'product_id' => 'product-template-foundation-1',
            'service_catalog_item_id' => 'service-template-foundation-1',
            'default_service_price_rupiah' => 50000,
            'default_package_total_rupiah' => 0,
            'is_active' => true,
            'sort_order' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertProductAndServiceCatalogItem(): void
    {
        DB::table('products')->insert([
            'id' => 'product-template-foundation-1',
            'kode_barang' => 'SPT-FOUND-001',
            'nama_barang' => 'Kampas Rem Template',
            'merek' => 'Federal',
            'ukuran' => null,
            'harga_jual' => 100000,
            'nama_barang_normalized' => 'kampas rem template',
            'merek_normalized' => 'federal',
        ]);

        DB::table('service_catalog_items')->insert([
            'id' => 'service-template-foundation-1',
            'name' => 'Jasa Pasang Template',
            'normalized_name' => 'jasa pasang template',
            'default_price_rupiah' => 50000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertInsertFails(array $payload): void
    {
        try {
            DB::table('service_product_templates')->insert($payload);
        } catch (QueryException) {
            $this->addToAssertionCount(1);

            return;
        }

        self::fail('Expected service_product_templates insert to fail.');
    }

    private function skipUnlessMysqlOrMariaDb(): void
    {
        $driver = DB::getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('MySQL/MariaDB metadata assertions only.');
        }
    }

    /**
     * @param list<string> $expectedColumns
     */
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
