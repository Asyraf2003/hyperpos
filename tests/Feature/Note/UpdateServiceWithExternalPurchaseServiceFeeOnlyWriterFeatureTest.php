<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Note\WorkItemWriterPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UpdateServiceWithExternalPurchaseServiceFeeOnlyWriterFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_service_fee_without_touching_external_purchase_lines(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => '2026-04-02',
            'total_rupiah' => 7000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'wi-1',
            'note_id' => 'note-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 7000,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => 'wi-1',
            'service_name' => 'Servis Lama',
            'service_price_rupiah' => 5000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        DB::table('work_item_external_purchase_lines')->insert([
            'id' => 'ext-1',
            'work_item_id' => 'wi-1',
            'cost_description' => 'Beli luar',
            'unit_cost_rupiah' => 2000,
            'qty' => 1,
            'line_total_rupiah' => 2000,
        ]);

        $updated = WorkItem::createServiceWithExternalPurchase(
            'wi-1',
            'note-1',
            1,
            ServiceDetail::create('Servis Baru', Money::fromInt(6000), ServiceDetail::PART_SOURCE_NONE),
            [ExternalPurchaseLine::create('ext-1', 'Beli luar', Money::fromInt(2000), 1)],
        );

        app(WorkItemWriterPort::class)->updateServiceWithExternalPurchaseServiceFeeOnly($updated);

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-1',
            'subtotal_rupiah' => 8000,
        ]);

        $this->assertDatabaseHas('work_item_service_details', [
            'work_item_id' => 'wi-1',
            'service_name' => 'Servis Baru',
            'service_price_rupiah' => 6000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        $this->assertDatabaseHas('work_item_external_purchase_lines', [
            'id' => 'ext-1',
            'work_item_id' => 'wi-1',
            'cost_description' => 'Beli luar',
            'unit_cost_rupiah' => 2000,
            'qty' => 1,
            'line_total_rupiah' => 2000,
        ]);
    }
}
