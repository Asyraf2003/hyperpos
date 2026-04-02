<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Application\Note\UseCases\CorrectPaidServiceWithExternalPurchaseServiceFeeOnlyHandler;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CorrectPaidServiceWithExternalPurchaseServiceFeeOnlyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_corrects_service_fee_only_and_keeps_external_purchase_lines_intact(): void
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

        DB::table('customer_payments')->insert([
            'id' => 'pay-1',
            'amount_rupiah' => 7000,
            'paid_at' => '2026-04-02',
        ]);

        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'pca-1',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-1',
                'component_type' => 'service_external_purchase_part',
                'component_ref_id' => 'ext-1',
                'component_amount_rupiah_snapshot' => 2000,
                'allocated_amount_rupiah' => 2000,
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

        $result = app(CorrectPaidServiceWithExternalPurchaseServiceFeeOnlyHandler::class)->handle(
            'note-1',
            1,
            'Servis Baru',
            4000,
            ServiceDetail::PART_SOURCE_NONE,
            'Koreksi fee jasa external',
            'actor-1',
        );

        $this->assertTrue($result->isSuccess());
        $this->assertSame(1000, $result->data()['refund_required_rupiah']);

        $this->assertDatabaseHas('notes', [
            'id' => 'note-1',
            'total_rupiah' => 6000,
        ]);

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-1',
            'subtotal_rupiah' => 6000,
        ]);

        $this->assertDatabaseHas('work_item_service_details', [
            'work_item_id' => 'wi-1',
            'service_name' => 'Servis Baru',
            'service_price_rupiah' => 4000,
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

        $this->assertDatabaseHas('note_mutation_events', [
            'note_id' => 'note-1',
            'mutation_type' => 'paid_service_with_external_purchase_service_fee_only_corrected',
            'actor_id' => 'actor-1',
            'reason' => 'Koreksi fee jasa external',
        ]);
    }
}
