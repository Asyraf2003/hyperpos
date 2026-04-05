<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Application\Payment\UseCases\RecordCustomerRefundHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RecordCustomerRefundFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_refund_component_allocations_in_reverse_allocation_order(): void
    {
        $this->seedNote();
        $this->seedPaymentAndAllocations();

        $result = app(RecordCustomerRefundHandler::class)->handle(
            'payment-1',
            'note-1',
            4000,
            '2026-04-03',
            'Refund jasa',
            'actor-1',
        );

        $this->assertTrue($result->isSuccess());
        $refundId = (string) DB::table('customer_refunds')->value('id');

        $this->assertDatabaseHas('refund_component_allocations', [
            'customer_refund_id' => $refundId,
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'component_type' => 'service_fee',
            'component_ref_id' => 'wi-2',
            'refunded_amount_rupiah' => 4000,
        ]);

        $this->assertDatabaseMissing('refund_component_allocations', [
            'customer_refund_id' => $refundId,
            'component_type' => 'product_only_work_item',
        ]);
    }

    public function test_it_rejects_refund_that_exceeds_remaining_pair_allocation(): void
    {
        $this->seedNote();
        $this->seedPaymentAndAllocations();

        $first = app(RecordCustomerRefundHandler::class)->handle(
            'payment-1',
            'note-1',
            4000,
            '2026-04-03',
            'Refund jasa',
            'actor-1',
        );
        $this->assertTrue($first->isSuccess());

        $second = app(RecordCustomerRefundHandler::class)->handle(
            'payment-1',
            'note-1',
            11000,
            '2026-04-03',
            'Refund berlebih',
            'actor-1',
        );

        $this->assertFalse($second->isSuccess());
        $this->assertDatabaseCount('customer_refunds', 1);
    }

    private function seedNote(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => '2026-04-02',
            'total_rupiah' => 14000,
        ]);
    }

    private function seedPaymentAndAllocations(): void
    {
        DB::table('customer_payments')->insert([
            'id' => 'payment-1',
            'amount_rupiah' => 14000,
            'paid_at' => '2026-04-02',
        ]);

        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'pa-1',
                'customer_payment_id' => 'payment-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-1',
                'component_type' => 'product_only_work_item',
                'component_ref_id' => 'wi-1',
                'component_amount_rupiah_snapshot' => 5000,
                'allocated_amount_rupiah' => 5000,
                'allocation_priority' => 1,
            ],
            [
                'id' => 'pa-2',
                'customer_payment_id' => 'payment-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-2',
                'component_type' => 'service_store_stock_part',
                'component_ref_id' => 'sto-2',
                'component_amount_rupiah_snapshot' => 3000,
                'allocated_amount_rupiah' => 3000,
                'allocation_priority' => 2,
            ],
            [
                'id' => 'pa-3',
                'customer_payment_id' => 'payment-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-3',
                'component_type' => 'service_external_purchase_part',
                'component_ref_id' => 'ext-1',
                'component_amount_rupiah_snapshot' => 2000,
                'allocated_amount_rupiah' => 2000,
                'allocation_priority' => 3,
            ],
            [
                'id' => 'pa-4',
                'customer_payment_id' => 'payment-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-2',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-2',
                'component_amount_rupiah_snapshot' => 4000,
                'allocated_amount_rupiah' => 4000,
                'allocation_priority' => 4,
            ],
        ]);
    }
}
