<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class NoteCorrectionHistoryPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_note_detail_page_shows_correction_history_from_audit_log(): void
    {
        $this->loginAsKasir();
        $user = User::query()->create(['name' => 'Kasir', 'email' => 'cashier@example.test', 'password' => 'password']);
        DB::table('actor_accesses')->insert(['actor_id' => (string) $user->getAuthIdentifier(), 'role' => 'kasir']);
        DB::table('notes')->insert(['id' => 'note-1', 'customer_name' => 'Budi', 'transaction_date' => '2026-03-14', 'total_rupiah' => 50000]);
        DB::table('work_items')->insert(['id' => 'wi-1', 'note_id' => 'note-1', 'line_no' => 1, 'transaction_type' => WorkItem::TYPE_SERVICE_ONLY, 'status' => WorkItem::STATUS_OPEN, 'subtotal_rupiah' => 50000]);
        DB::table('work_item_service_details')->insert(['work_item_id' => 'wi-1', 'service_name' => 'Servis A', 'service_price_rupiah' => 50000, 'part_source' => ServiceDetail::PART_SOURCE_NONE]);
        DB::table('customer_payments')->insert(['id' => 'cp-1', 'amount_rupiah' => 50000, 'paid_at' => '2026-03-14']);
        DB::table('payment_allocations')->insert(['id' => 'pa-1', 'customer_payment_id' => 'cp-1', 'note_id' => 'note-1', 'amount_rupiah' => 50000]);

        DB::table('audit_logs')->insert([
            'event' => 'paid_service_only_work_item_corrected',
            'context' => json_encode([
                'note_id' => 'note-1',
                'performed_by_actor_id' => 'actor-1',
                'reason' => 'Harga salah input',
                'refund_required_rupiah' => 10000,
                'before' => ['note' => ['total_rupiah' => 50000]],
                'after' => ['note' => ['total_rupiah' => 40000]],
            ], JSON_THROW_ON_ERROR),
        ]);

        $response = $this->actingAs($user)->get('/cashier/notes/note-1');

        $response->assertOk();
        $response->assertSee('Riwayat Correction');
        $response->assertSee('Correction Nominal Service');
        $response->assertSee('Harga salah input');
        $response->assertSee('actor-1');
    }
}
