<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Adapters\Out\Reporting\Queries\TransactionSummaryReportingQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TransactionSummaryReportingQueryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reads_summary_from_component_ledgers(): void
    {
        $this->seedNote('note-1', 'Budi', '2026-04-02', 26000);
        $this->seedNote('note-2', 'Sari', '2026-04-03', 10000);

        $this->seedWorkItem('wi-1', 'note-1', 1, 5000);
        $this->seedWorkItem('wi-2', 'note-1', 2, 3000);
        $this->seedWorkItem('wi-3', 'note-2', 1, 10000);

        $this->seedCustomerPayment('pay-1', 8000, '2026-04-02');
        $this->seedCustomerPayment('pay-2', 4000, '2026-04-03');

        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'p1',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-1',
                'component_type' => 'product_only_work_item',
                'component_ref_id' => 'wi-1',
                'component_amount_rupiah_snapshot' => 5000,
                'allocated_amount_rupiah' => 5000,
                'allocation_priority' => 1,
            ],
            [
                'id' => 'p2',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-2',
                'component_type' => 'service_store_stock_part',
                'component_ref_id' => 'sto-2',
                'component_amount_rupiah_snapshot' => 3000,
                'allocated_amount_rupiah' => 3000,
                'allocation_priority' => 2,
            ],
            [
                'id' => 'p3',
                'customer_payment_id' => 'pay-2',
                'note_id' => 'note-2',
                'work_item_id' => 'wi-3',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-3',
                'component_amount_rupiah_snapshot' => 10000,
                'allocated_amount_rupiah' => 4000,
                'allocation_priority' => 1,
            ],
        ]);

        $this->seedCustomerRefund('ref-1', 'pay-1', 'note-1', 1000, '2026-04-04', 'Refund');

        DB::table('refund_component_allocations')->insert([
            [
                'id' => 'r1',
                'customer_refund_id' => 'ref-1',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-2',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-2',
                'refunded_amount_rupiah' => 1000,
                'refund_priority' => 1,
            ],
        ]);

        $query = app(TransactionSummaryReportingQuery::class);
        $rows = $query->rows('2026-04-01', '2026-04-30');
        $recon = $query->reconciliation('2026-04-01', '2026-04-30');

        $this->assertCount(2, $rows);
        $this->assertSame(8000, $rows[0]['allocated_payment_rupiah']);
        $this->assertSame(1000, $rows[0]['refunded_rupiah']);
        $this->assertSame(4000, $rows[1]['allocated_payment_rupiah']);
        $this->assertSame(0, $rows[1]['refunded_rupiah']);
        $this->assertSame(2, $recon['total_notes']);
        $this->assertSame(36000, $recon['gross_transaction_rupiah']);
        $this->assertSame(12000, $recon['allocated_payment_rupiah']);
        $this->assertSame(1000, $recon['refunded_rupiah']);
    }

    private function seedNote(string $id, string $customerName, string $transactionDate, int $totalRupiah): void
    {
        DB::table('notes')->insert([
            'id' => $id,
            'customer_name' => $customerName,
            'transaction_date' => $transactionDate,
            'total_rupiah' => $totalRupiah,
        ]);
    }

    private function seedWorkItem(string $id, string $noteId, int $lineNo, int $subtotalRupiah): void
    {
        DB::table('work_items')->insert([
            'id' => $id,
            'note_id' => $noteId,
            'line_no' => $lineNo,
            'transaction_type' => 'service_only',
            'status' => 'open',
            'subtotal_rupiah' => $subtotalRupiah,
        ]);
    }

    private function seedCustomerPayment(string $id, int $amountRupiah, string $paidAt): void
    {
        DB::table('customer_payments')->insert([
            'id' => $id,
            'amount_rupiah' => $amountRupiah,
            'paid_at' => $paidAt,
        ]);
    }

    private function seedCustomerRefund(
        string $id,
        string $paymentId,
        string $noteId,
        int $amountRupiah,
        string $refundedAt,
        string $reason
    ): void {
        DB::table('customer_refunds')->insert([
            'id' => $id,
            'customer_payment_id' => $paymentId,
            'note_id' => $noteId,
            'amount_rupiah' => $amountRupiah,
            'refunded_at' => $refundedAt,
            'reason' => $reason,
        ]);
    }
}
