<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class V2HotPathIndexesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_hot_path_indexes_exist_on_target_tables(): void
    {
        $this->skipUnlessMysqlOrMariaDb();

        $this->assertIndexColumns('audit_logs', 'audit_logs_event_idx', ['event']);

        $this->assertIndexColumns('products', 'products_merek_idx', ['merek']);
        $this->assertIndexColumns('products', 'products_ukuran_idx', ['ukuran']);
        $this->assertIndexColumns('products', 'products_harga_jual_idx', ['harga_jual']);
        $this->assertIndexColumns('products', 'products_duplicate_lookup_idx', ['nama_barang', 'merek', 'ukuran']);

        $this->assertIndexColumns('payment_allocations', 'payment_allocations_payment_note_idx', ['customer_payment_id', 'note_id']);

        $this->assertIndexColumns('payment_component_allocations', 'pca_payment_note_idx', ['customer_payment_id', 'note_id']);
        $this->assertIndexColumns('payment_component_allocations', 'pca_work_item_idx', ['work_item_id']);

        $this->assertIndexColumns('refund_component_allocations', 'rca_work_item_idx', ['work_item_id']);
    }

    private function skipUnlessMysqlOrMariaDb(): void
    {
        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
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
