<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Adapters\Out\Reporting\Queries\TransactionCashLedgerReportingQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TransactionCashLedgerReportingQueryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_cash_events_from_component_ledgers(): void
    {
        DB::table('customer_payments')->insert([
            ['id' => 'pay-1', 'amount_rupiah' => 8000, 'paid_at' => '2026-04-02'],
            ['id' => 'pay-2', 'amount_rupiah' => 4000, 'paid_at' => '2026-04-03'],
        ]);

        DB::table('customer_refunds')->insert([
            ['id' => 'ref-1', 'customer_payment_id' => 'pay-1', 'note_id' => 'note-1', 'amount_rupiah' => 1000, 'refunded_at' => '2026-04-04', 'reason' => 'Refund'],
        ]);

        DB::table('payment_component_allocations')->insert([
            ['id' => 'p1', 'customer_payment_id' => 'pay-1', 'note_id' => 'note-1', 'work_item_id' => 'wi-1', 'component_type' => 'product_only_work_item', 'component_ref_id' => 'wi-1', 'component_amount_rupiah_snapshot' => 5000, 'allocated_amount_rupiah' => 5000, 'allocation_priority' => 1],
            ['id' => 'p2', 'customer_payment_id' => 'pay-1', 'note_id' => 'note-1', 'work_item_id' => 'wi-2', 'component_type' => 'service_store_stock_part', 'component_ref_id' => 'sto-2', 'component_amount_rupiah_snapshot' => 3000, 'allocated_amount_rupiah' => 3000, 'allocation_priority' => 2],
            ['id' => 'p3', 'customer_payment_id' => 'pay-2', 'note_id' => 'note-2', 'work_item_id' => 'wi-3', 'component_type' => 'service_fee', 'component_ref_id' => 'wi-3', 'component_amount_rupiah_snapshot' => 10000, 'allocated_amount_rupiah' => 4000, 'allocation_priority' => 1],
        ]);

        DB::table('refund_component_allocations')->insert([
            ['id' => 'r1', 'customer_refund_id' => 'ref-1', 'customer_payment_id' => 'pay-1', 'note_id' => 'note-1', 'work_item_id' => 'wi-2', 'component_type' => 'service_fee', 'component_ref_id' => 'wi-2', 'refunded_amount_rupiah' => 1000, 'refund_priority' => 1],
        ]);

        $query = app(TransactionCashLedgerReportingQuery::class);
        $rows = $query->rows('2026-04-01', '2026-04-30');
        $recon = $query->reconciliation('2026-04-01', '2026-04-30');

        $this->assertCount(3, $rows);
        $this->assertSame('payment_allocation', $rows[0]['event_type']);
        $this->assertSame(8000, $rows[0]['event_amount_rupiah']);
        $this->assertSame('payment_allocation', $rows[1]['event_type']);
        $this->assertSame(4000, $rows[1]['event_amount_rupiah']);
        $this->assertSame('refund', $rows[2]['event_type']);
        $this->assertSame(1000, $rows[2]['event_amount_rupiah']);
        $this->assertSame(12000, $recon['total_in_rupiah']);
        $this->assertSame(1000, $recon['total_out_rupiah']);
    }
}
