<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Application\Note\UseCases\CorrectPaidServiceWithStoreStockPartServiceFeeOnlyHandler;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CorrectPaidServiceWithStoreStockPartServiceFeeOnlyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_corrects_service_fee_only_and_keeps_store_stock_lines_intact(): void
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

        DB::table('customer_payments')->insert([
            'id' => 'pay-1',
            'amount_rupiah' => 8000,
            'paid_at' => '2026-04-02',
        ]);

        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'pca-1',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-1',
                'component_type' => 'service_store_stock_part',
                'component_ref_id' => 'sto-1',
                'component_amount_rupiah_snapshot' => 3000,
                'allocated_amount_rupiah' => 3000,
                'allocation_priority' => 1,
            ],
            [
                'id' => 'pca-2',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-1',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-1',
                'component_amount_rupiah_snapshot' => 5000,
                'allocated_amount_rupiah' => 5000,
                'allocation_priority' => 2,
            ],
        ]);

        $result = app(CorrectPaidServiceWithStoreStockPartServiceFeeOnlyHandler::class)->handle(
            'note-1',
            1,
            'Servis Baru',
            4000,
            ServiceDetail::PART_SOURCE_NONE,
            'Koreksi fee jasa',
            'actor-1',
        );

        $this->assertTrue($result->isSuccess());
        $this->assertSame(1000, $result->data()['refund_required_rupiah']);

        $this->assertDatabaseHas('notes', [
            'id' => 'note-1',
            'total_rupiah' => 7000,
        ]);

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-1',
            'subtotal_rupiah' => 7000,
        ]);

        $this->assertDatabaseHas('work_item_service_details', [
            'work_item_id' => 'wi-1',
            'service_name' => 'Servis Baru',
            'service_price_rupiah' => 4000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        $this->assertDatabaseHas('work_item_store_stock_lines', [
            'id' => 'sto-1',
            'work_item_id' => 'wi-1',
            'product_id' => 'product-1',
            'qty' => 1,
            'line_total_rupiah' => 3000,
        ]);

        $this->assertDatabaseHas('note_mutation_events', [
            'note_id' => 'note-1',
            'mutation_type' => 'paid_service_with_store_stock_part_service_fee_only_corrected',
            'actor_id' => 'actor-1',
            'reason' => 'Koreksi fee jasa',
        ]);
    }
}
