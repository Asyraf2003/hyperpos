<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;
use App\Ports\Out\Note\WorkItemWriterPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UpdateServiceWithStoreStockPartServiceFeeOnlyWriterFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_service_fee_without_touching_store_stock_lines(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => '2026-04-02',
            'total_rupiah' => 8000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'wi-1',
            'note_id' => 'note-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 8000,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => 'wi-1',
            'service_name' => 'Servis Lama',
            'service_price_rupiah' => 5000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        DB::table('work_item_store_stock_lines')->insert([
            'id' => 'sto-1',
            'work_item_id' => 'wi-1',
            'product_id' => 'product-1',
            'qty' => 1,
            'line_total_rupiah' => 3000,
        ]);

        $updated = WorkItem::createServiceWithStoreStockPart(
            'wi-1',
            'note-1',
            1,
            ServiceDetail::create('Servis Baru', \App\Core\Shared\ValueObjects\Money::fromInt(7000), ServiceDetail::PART_SOURCE_NONE),
            [StoreStockLine::create('sto-1', 'product-1', 1, \App\Core\Shared\ValueObjects\Money::fromInt(3000))],
        );

        app(WorkItemWriterPort::class)->updateServiceWithStoreStockPartServiceFeeOnly($updated);

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-1',
            'subtotal_rupiah' => 10000,
        ]);

        $this->assertDatabaseHas('work_item_service_details', [
            'work_item_id' => 'wi-1',
            'service_name' => 'Servis Baru',
            'service_price_rupiah' => 7000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        $this->assertDatabaseHas('work_item_store_stock_lines', [
            'id' => 'sto-1',
            'work_item_id' => 'wi-1',
            'product_id' => 'product-1',
            'qty' => 1,
            'line_total_rupiah' => 3000,
        ]);
    }
}
