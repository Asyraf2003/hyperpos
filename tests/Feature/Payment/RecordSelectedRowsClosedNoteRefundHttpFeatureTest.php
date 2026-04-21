<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class RecordSelectedRowsClosedNoteRefundHttpFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_it_records_refund_for_selected_close_row_even_when_note_still_has_open_line(): void
    {
        $user = $this->seedKasir(); $today = date('Y-m-d'); $this->seedOpenNoteWithOnePaidLine($today);

        $response = $this->actingAs($user)->post(route('cashier.notes.refunds.store', ['noteId' => 'note-1']), [
            'selected_row_ids' => ['wi-1'], 'customer_payment_id' => 'payment-1',
            'amount_rupiah' => 10000, 'refunded_at' => $today, 'reason' => 'Refund line close',
        ]);

        $response->assertRedirect(route('cashier.notes.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('refund_component_allocations', ['customer_payment_id' => 'payment-1', 'note_id' => 'note-1', 'work_item_id' => 'wi-1', 'refunded_amount_rupiah' => 10000]);
        $this->assertDatabaseMissing('refund_component_allocations', ['note_id' => 'note-1', 'work_item_id' => 'wi-2']);
    }

    public function test_open_note_with_close_line_shows_refund_launcher(): void
    {
        $user = $this->seedKasir(); $today = date('Y-m-d'); $this->seedOpenNoteWithOnePaidLine($today);

        $this->actingAs($user)->get(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->assertOk()->assertSee('Buka Modal Refund')->assertSee('Refund Nota')
            ->assertDontSee('Refund Line Close Terpilih')->assertDontSee('Panel Refund');
    }

    private function seedKasir(): User
    {
        $this->loginAsKasir();
        $user = User::query()->create(['name' => 'Kasir Refund Selected Rows', 'email' => 'kasir-refund-selected-rows@example.test', 'password' => 'password']);
        DB::table('actor_accesses')->insert(['actor_id' => (string) $user->getAuthIdentifier(), 'role' => 'kasir']);
        return $user;
    }

    private function seedOpenNoteWithOnePaidLine(string $today): void
    {
        $this->seedNoteBase('note-1', 'Budi', $today, 100000, 'open');
        $this->seedWorkItemBase('wi-1', 'note-1', 1, WorkItem::TYPE_SERVICE_ONLY, WorkItem::STATUS_OPEN, 50000);
        $this->seedServiceDetailBase('wi-1', 'Servis A', 50000, ServiceDetail::PART_SOURCE_NONE);
        $this->seedWorkItemBase('wi-2', 'note-1', 2, WorkItem::TYPE_SERVICE_ONLY, WorkItem::STATUS_OPEN, 50000);
        $this->seedServiceDetailBase('wi-2', 'Servis B', 50000, ServiceDetail::PART_SOURCE_NONE);
        $this->seedCustomerPaymentBase('payment-1', 50000, $today);
        DB::table('payment_component_allocations')->insert(['id' => 'pca-1', 'customer_payment_id' => 'payment-1', 'note_id' => 'note-1', 'work_item_id' => 'wi-1', 'component_type' => 'service_fee', 'component_ref_id' => 'wi-1', 'component_amount_rupiah_snapshot' => 50000, 'allocated_amount_rupiah' => 50000, 'allocation_priority' => 1]);
    }
}
