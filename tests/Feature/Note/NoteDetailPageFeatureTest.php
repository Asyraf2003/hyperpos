<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class NoteDetailPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_see_row_settlement_labels_and_outstanding_values(): void
    {
        $this->loginAsKasir();
        $user = User::query()->create([
            'name' => 'Kasir Detail',
            'email' => 'cashier-note-detail@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        $today = now()->toDateString();

        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => $today,
            'total_rupiah' => 26000,
            'note_state' => 'open',
        ]);

        DB::table('work_items')->insert([
            ['id' => 'wi-1', 'note_id' => 'note-1', 'line_no' => 1, 'transaction_type' => WorkItem::TYPE_STORE_STOCK_SALE_ONLY, 'status' => WorkItem::STATUS_OPEN, 'subtotal_rupiah' => 5000],
            ['id' => 'wi-2', 'note_id' => 'note-1', 'line_no' => 2, 'transaction_type' => WorkItem::TYPE_SERVICE_WITH_STORE_STOCK_PART, 'status' => WorkItem::STATUS_OPEN, 'subtotal_rupiah' => 8000],
            ['id' => 'wi-3', 'note_id' => 'note-1', 'line_no' => 3, 'transaction_type' => WorkItem::TYPE_SERVICE_ONLY, 'status' => WorkItem::STATUS_OPEN, 'subtotal_rupiah' => 13000],
        ]);

        DB::table('work_item_service_details')->insert([
            ['work_item_id' => 'wi-2', 'service_name' => 'Servis A', 'service_price_rupiah' => 5000, 'part_source' => ServiceDetail::PART_SOURCE_NONE],
            ['work_item_id' => 'wi-3', 'service_name' => 'Servis B', 'service_price_rupiah' => 13000, 'part_source' => ServiceDetail::PART_SOURCE_NONE],
        ]);

        DB::table('work_item_store_stock_lines')->insert([
            ['id' => 'sto-1', 'work_item_id' => 'wi-1', 'product_id' => 'product-1', 'qty' => 1, 'line_total_rupiah' => 5000],
            ['id' => 'sto-2', 'work_item_id' => 'wi-2', 'product_id' => 'product-2', 'qty' => 1, 'line_total_rupiah' => 3000],
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'pay-1',
            'amount_rupiah' => 8000,
            'paid_at' => $today,
        ]);

        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'pca-1',
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
                'id' => 'pca-2',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-2',
                'component_type' => 'service_store_stock_part',
                'component_ref_id' => 'sto-2',
                'component_amount_rupiah_snapshot' => 3000,
                'allocated_amount_rupiah' => 3000,
                'allocation_priority' => 2,
            ],
        ]);

        $response = $this->actingAs($user)->get(route('cashier.notes.show', ['noteId' => 'note-1']));

        $response->assertOk();
        $response->assertSee('Sudah Dibayar');
        $response->assertSee('Sisa Tagihan');
        $response->assertSee('8.000', false);
        $response->assertSee('18.000', false);
        $response->assertSee('3.000', false);
        $response->assertSee('13.000', false);
    }
}
