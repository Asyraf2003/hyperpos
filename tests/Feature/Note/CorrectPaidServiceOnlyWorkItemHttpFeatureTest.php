<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CorrectPaidServiceOnlyWorkItemHttpFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_correct_paid_service_only_work_item_via_http(): void
    {
        $this->loginAsKasir();
        $user = User::query()->create(['name' => 'Kasir', 'email' => 'cashier@example.test', 'password' => 'password']);
        DB::table('actor_accesses')->insert(['actor_id' => (string) $user->getAuthIdentifier(), 'role' => 'kasir']);
        DB::table('notes')->insert(['id' => 'note-1', 'customer_name' => 'Budi', 'transaction_date' => '2026-03-14', 'total_rupiah' => 50000]);
        DB::table('work_items')->insert(['id' => 'wi-1', 'note_id' => 'note-1', 'line_no' => 1, 'transaction_type' => WorkItem::TYPE_SERVICE_ONLY, 'status' => WorkItem::STATUS_OPEN, 'subtotal_rupiah' => 50000]);
        DB::table('work_item_service_details')->insert(['work_item_id' => 'wi-1', 'service_name' => 'Servis A', 'service_price_rupiah' => 50000, 'part_source' => ServiceDetail::PART_SOURCE_NONE]);
        DB::table('customer_payments')->insert(['id' => 'cp-1', 'amount_rupiah' => 50000, 'paid_at' => '2026-03-14']);
        DB::table('payment_allocations')->insert(['id' => 'pa-1', 'customer_payment_id' => 'cp-1', 'note_id' => 'note-1', 'amount_rupiah' => 50000]);

        $response = $this->actingAs($user)->post('/cashier/notes/note-1/corrections/service-only', [
            'line_no' => 1,
            'service_name' => 'Servis Koreksi',
            'service_price_rupiah' => 40000,
            'part_source' => ServiceDetail::PART_SOURCE_CUSTOMER_OWNED,
            'reason' => 'Harga awal terlalu tinggi.',
        ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $this->assertDatabaseHas('work_item_service_details', ['work_item_id' => 'wi-1', 'service_name' => 'Servis Koreksi', 'service_price_rupiah' => 40000, 'part_source' => ServiceDetail::PART_SOURCE_CUSTOMER_OWNED]);
        $this->assertDatabaseHas('notes', ['id' => 'note-1', 'total_rupiah' => 40000]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'paid_service_only_work_item_corrected']);
    }
}
