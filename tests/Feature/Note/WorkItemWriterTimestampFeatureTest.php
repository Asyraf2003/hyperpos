<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Note\WorkItemWriterPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class WorkItemWriterTimestampFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_system_timestamps_on_created_work_item_group_rows(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-0011',
            'customer_name' => 'Budi',
            'transaction_date' => '2026-05-15',
            'total_rupiah' => 0,
        ]);

        DB::table('products')->insert([
            'id' => 'product-0011',
            'kode_barang' => 'KB-0011',
            'nama_barang' => 'Oli Mesin',
            'merek' => 'Federal',
            'ukuran' => null,
            'harga_jual' => 15000,
        ]);

        $writer = app(WorkItemWriterPort::class);

        $writer->create(WorkItem::createServiceWithExternalPurchase(
            'wi-ext-0011',
            'note-0011',
            1,
            ServiceDetail::create('Servis External', Money::fromInt(5000), ServiceDetail::PART_SOURCE_NONE),
            [
                ExternalPurchaseLine::create('ext-0011', 'Beli luar', Money::fromInt(2000), 1),
            ],
        ));

        $writer->create(WorkItem::createServiceWithStoreStockPart(
            'wi-stock-0011',
            'note-0011',
            2,
            ServiceDetail::create('Servis Stock', Money::fromInt(7000), ServiceDetail::PART_SOURCE_NONE),
            [
                StoreStockLine::create('sto-0011', 'product-0011', 1, Money::fromInt(3000)),
            ],
        ));

        $this->assertRowHasTimestamps('work_items', 'id', 'wi-ext-0011');
        $this->assertRowHasTimestamps('work_items', 'id', 'wi-stock-0011');
        $this->assertRowHasTimestamps('work_item_service_details', 'work_item_id', 'wi-ext-0011');
        $this->assertRowHasTimestamps('work_item_service_details', 'work_item_id', 'wi-stock-0011');
        $this->assertRowHasTimestamps('work_item_external_purchase_lines', 'id', 'ext-0011');
        $this->assertRowHasTimestamps('work_item_store_stock_lines', 'id', 'sto-0011');
    }

    private function assertRowHasTimestamps(string $table, string $keyColumn, string $keyValue): void
    {
        $row = DB::table($table)
            ->where($keyColumn, $keyValue)
            ->select([$keyColumn, 'created_at', 'updated_at'])
            ->first();

        $this->assertNotNull($row, "Missing row {$table}.{$keyColumn}={$keyValue}");
        $this->assertNotNull($row->created_at, "Missing {$table}.created_at");
        $this->assertNotNull($row->updated_at, "Missing {$table}.updated_at");
    }
}
