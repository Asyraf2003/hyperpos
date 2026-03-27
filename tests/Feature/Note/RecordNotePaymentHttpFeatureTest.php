<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RecordNotePaymentHttpFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_cashier_can_record_note_payment_for_selected_rows(): void
    {
        $this->loginAsKasir();
        $user = User::query()->create(['name' => 'Kasir Aktif', 'email' => 'cashier@example.test', 'password' => 'password']);
        DB::table('actor_accesses')->insert(['actor_id' => (string) $user->getAuthIdentifier(), 'role' => 'kasir']);

        DB::table('notes')->insert(['id' => 'note-1', 'customer_name' => 'Budi', 'transaction_date' => '2026-03-14', 'total_rupiah' => 150000]);
        DB::table('work_items')->insert([
            ['id' => 'wi-1', 'note_id' => 'note-1', 'line_no' => 1, 'transaction_type' => WorkItem::TYPE_SERVICE_ONLY, 'status' => WorkItem::STATUS_OPEN, 'subtotal_rupiah' => 50000],
            ['id' => 'wi-2', 'note_id' => 'note-1', 'line_no' => 2, 'transaction_type' => WorkItem::TYPE_SERVICE_ONLY, 'status' => WorkItem::STATUS_OPEN, 'subtotal_rupiah' => 100000],
        ]);
        DB::table('work_item_service_details')->insert([
            ['work_item_id' => 'wi-1', 'service_name' => 'Servis A', 'service_price_rupiah' => 50000, 'part_source' => ServiceDetail::PART_SOURCE_NONE],
            ['work_item_id' => 'wi-2', 'service_name' => 'Servis B', 'service_price_rupiah' => 100000, 'part_source' => ServiceDetail::PART_SOURCE_NONE],
        ]);

        $response = $this->actingAs($user)->post('/cashier/notes/note-1/payments', [
            'selected_row_ids' => ['wi-1'],
            'payment_method' => 'cash',
            'paid_at' => '2026-03-15',
            'amount_received' => 70000,
        ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $this->assertDatabaseHas('customer_payments', ['amount_rupiah' => 50000, 'paid_at' => '2026-03-15']);

        $paymentId = (string) DB::table('customer_payments')->value('id');
        $this->assertNotSame('', $paymentId);

        $this->assertDatabaseHas('payment_allocations', [
            'customer_payment_id' => $paymentId,
            'note_id' => 'note-1',
            'amount_rupiah' => 50000,
        ]);
    }
}
