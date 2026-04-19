<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\StoreStockLine;
use App\Core\Note\WorkItem\WorkItem;
use App\Ports\Out\Note\WorkItemWriterPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class UpdateServiceWithStoreStockPartServiceFeeOnlyWriterFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_it_updates_service_fee_without_touching_store_stock_lines(): void
    {
        $this->seedNotePaymentProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 100, 3000);
        $this->seedNoteBase('note-1', 'Budi', '2026-04-02', 8000);
        $this->seedWorkItemBase('wi-1', 'note-1', 1, WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART, WorkItem::STATUS_OPEN, 8000);
        $this->seedServiceDetailBase('wi-1', 'Servis Lama', 5000, ServiceDetail::PART_SOURCE_NONE);
        $this->seedStoreStockLineBase('sto-1', 'wi-1', 'product-1', 1, 3000);

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
