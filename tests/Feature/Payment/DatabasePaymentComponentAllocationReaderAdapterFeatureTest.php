<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Adapters\Out\Payment\DatabasePaymentComponentAllocationReaderAdapter;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class DatabasePaymentComponentAllocationReaderAdapterFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_note_total_includes_component_refunds_for_revised_note_component_flow(): void
    {
        $today = date('Y-m-d');

        $this->seedNoteBase('note-1', 'Budi', $today, 200000, 'open');
        $this->seedCustomerPaymentBase('payment-1', 300000, $today);
        $this->seedWorkItemBase(
            'wi-old-1',
            'note-1',
            1,
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            WorkItem::STATUS_CANCELED,
            100000,
        );
        $this->seedWorkItemBase(
            'wi-new-1',
            'note-1',
            1,
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            WorkItem::STATUS_OPEN,
            200000,
        );

        DB::table('payment_component_allocations')->insert([
            'id' => 'pca-active-1',
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'work_item_id' => 'wi-new-1',
            'component_type' => WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            'component_ref_id' => 'wi-new-1',
            'component_amount_rupiah_snapshot' => 200000,
            'allocated_amount_rupiah' => 200000,
            'allocation_priority' => 1,
        ]);

        DB::table('customer_refunds')->insert([
            'id' => 'refund-old-1',
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'amount_rupiah' => 100000,
            'refunded_at' => $today,
            'reason' => 'Refund historical row before revision',
        ]);

        DB::table('refund_component_allocations')->insert([
            'id' => 'rca-old-1',
            'customer_refund_id' => 'refund-old-1',
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'work_item_id' => 'wi-old-1',
            'component_type' => WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            'component_ref_id' => 'wi-old-1',
            'refunded_amount_rupiah' => 100000,
            'refund_priority' => 1,
        ]);

        $reader = new DatabasePaymentComponentAllocationReaderAdapter();

        self::assertSame(
            300000,
            $reader->getTotalAllocatedAmountByNoteId('note-1')->amount(),
        );
    }
}
