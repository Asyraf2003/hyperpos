<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class RevisionAfterRefundPreservesHistoricalWorkItemsFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_revision_after_refund_preserves_old_work_item_as_historical_anchor(): void
    {
        $user = $this->loginAsAuthorizedAdmin();
        $oldDate = date('Y-m-d', strtotime('-4 days'));
        $today = date('Y-m-d');

        $this->seedRefundedProductOnlyNote($oldDate);

        $response = $this->actingAs($user)->patch(
            route('admin.notes.workspace.update', ['noteId' => 'note-refund-revision-1']),
            [
                'note' => [
                    'customer_name' => 'Budi Refund Revised',
                    'customer_phone' => '08123456789',
                    'transaction_date' => $today,
                ],
                'items' => [
                    [
                        'entry_mode' => 'product',
                        'description' => null,
                        'part_source' => 'store_stock',
                        'service' => [
                            'name' => null,
                            'price_rupiah' => null,
                            'notes' => null,
                        ],
                        'product_lines' => [
                            [
                                'product_id' => 'product-refund-revision-1',
                                'qty' => 2,
                                'unit_price_rupiah' => 100000,
                                'price_basis' => 'revision_snapshot',
                            ],
                        ],
                        'external_purchase_lines' => [],
                    ],
                ],
                'inline_payment' => [
                    'decision' => 'skip',
                    'payment_method' => null,
                    'paid_at' => null,
                    'amount_paid_rupiah' => null,
                    'amount_received_rupiah' => null,
                ],
            ],
        );

        $response->assertRedirect(route('admin.notes.show', ['noteId' => 'note-refund-revision-1']));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-refund-revision-old-1',
            'note_id' => 'note-refund-revision-1',
        ]);

        $this->assertDatabaseHas('refund_component_allocations', [
            'id' => 'rca-refund-revision-old-1',
            'customer_refund_id' => 'refund-refund-revision-1',
            'customer_payment_id' => 'payment-refund-revision-1',
            'note_id' => 'note-refund-revision-1',
            'work_item_id' => 'wi-refund-revision-old-1',
            'component_type' => 'product_only_work_item',
            'component_ref_id' => 'wi-refund-revision-old-1',
            'refunded_amount_rupiah' => 100000,
        ]);

        $newWorkItem = DB::table('work_items')
            ->where('note_id', 'note-refund-revision-1')
            ->where('transaction_type', WorkItem::TYPE_STORE_STOCK_SALE_ONLY)
            ->where('id', '!=', 'wi-refund-revision-old-1')
            ->first();

        $this->assertNotNull($newWorkItem);
        $this->assertSame(200000, (int) $newWorkItem->subtotal_rupiah);

        $newStoreLine = DB::table('work_item_store_stock_lines')
            ->where('work_item_id', (string) $newWorkItem->id)
            ->where('product_id', 'product-refund-revision-1')
            ->first();

        $this->assertNotNull($newStoreLine);
        $this->assertSame(2, (int) $newStoreLine->qty);
        $this->assertSame(200000, (int) $newStoreLine->line_total_rupiah);

        $this->assertDatabaseHas('notes', [
            'id' => 'note-refund-revision-1',
            'customer_name' => 'Budi Refund Revised',
            'transaction_date' => $today,
            'total_rupiah' => 200000,
            'latest_revision_number' => 2,
        ]);
    }

    private function seedRefundedProductOnlyNote(string $oldDate): void
    {
        $this->seedNoteBase('note-refund-revision-1', 'Budi Refund Lama', $oldDate, 300000, 'closed');
        $this->seedNotePaymentProduct(
            'product-refund-revision-1',
            'PRD-RFD-1',
            'Produk Refund Revision',
            'Merek Refund',
            100,
            100000
        );

        $this->seedWorkItemBase(
            'wi-refund-revision-old-1',
            'note-refund-revision-1',
            1,
            WorkItem::TYPE_STORE_STOCK_SALE_ONLY,
            WorkItem::STATUS_OPEN,
            300000
        );

        $this->seedStoreStockLineBase(
            'ssl-refund-revision-old-1',
            'wi-refund-revision-old-1',
            'product-refund-revision-1',
            3,
            300000
        );

        DB::table('note_revisions')->insert([
            'id' => 'note-refund-revision-1-r001',
            'note_root_id' => 'note-refund-revision-1',
            'revision_number' => 1,
            'parent_revision_id' => null,
            'created_by_actor_id' => null,
            'reason' => 'seed refunded note before revision',
            'customer_name' => 'Budi Refund Lama',
            'customer_phone' => null,
            'transaction_date' => $oldDate,
            'grand_total_rupiah' => 300000,
            'line_count' => 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => null,
        ]);

        DB::table('notes')
            ->where('id', 'note-refund-revision-1')
            ->update([
                'current_revision_id' => 'note-refund-revision-1-r001',
                'latest_revision_number' => 1,
            ]);

        DB::table('product_inventory')->insert([
            'product_id' => 'product-refund-revision-1',
            'qty_on_hand' => 7,
        ]);

        DB::table('product_inventory_costing')->insert([
            'product_id' => 'product-refund-revision-1',
            'avg_cost_rupiah' => 60000,
            'inventory_value_rupiah' => 420000,
        ]);

        DB::table('inventory_movements')->insert([
            'id' => 'move-refund-revision-old-1',
            'product_id' => 'product-refund-revision-1',
            'movement_type' => 'stock_out',
            'source_type' => 'work_item_store_stock_line',
            'source_id' => 'ssl-refund-revision-old-1',
            'tanggal_mutasi' => $oldDate,
            'qty_delta' => -3,
            'unit_cost_rupiah' => 60000,
            'total_cost_rupiah' => -180000,
        ]);

        $this->seedCustomerPaymentBase('payment-refund-revision-1', 300000, $oldDate);
        $this->seedPaymentAllocationBase(
            'payment-allocation-refund-revision-1',
            'payment-refund-revision-1',
            'note-refund-revision-1',
            300000
        );

        DB::table('payment_component_allocations')->insert([
            'id' => 'pca-refund-revision-old-1',
            'customer_payment_id' => 'payment-refund-revision-1',
            'note_id' => 'note-refund-revision-1',
            'work_item_id' => 'wi-refund-revision-old-1',
            'component_type' => 'product_only_work_item',
            'component_ref_id' => 'wi-refund-revision-old-1',
            'component_amount_rupiah_snapshot' => 300000,
            'allocated_amount_rupiah' => 300000,
            'allocation_priority' => 1,
        ]);

        DB::table('customer_refunds')->insert([
            'id' => 'refund-refund-revision-1',
            'customer_payment_id' => 'payment-refund-revision-1',
            'note_id' => 'note-refund-revision-1',
            'amount_rupiah' => 100000,
            'refunded_at' => $oldDate,
            'reason' => 'Refund sebelum revision',
        ]);

        DB::table('refund_component_allocations')->insert([
            'id' => 'rca-refund-revision-old-1',
            'customer_refund_id' => 'refund-refund-revision-1',
            'customer_payment_id' => 'payment-refund-revision-1',
            'note_id' => 'note-refund-revision-1',
            'work_item_id' => 'wi-refund-revision-old-1',
            'component_type' => 'product_only_work_item',
            'component_ref_id' => 'wi-refund-revision-old-1',
            'refunded_amount_rupiah' => 100000,
            'refund_priority' => 1,
        ]);
    }
}
