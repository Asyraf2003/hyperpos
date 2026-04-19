<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class EditTransactionWorkspacePageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_open_edit_workspace_page_for_unpaid_note_with_payment_modal_but_without_refund_action(): void
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Edit Workspace',
            'email' => 'edit-workspace-page@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        DB::table('notes')->insert([
            'id' => 'note-edit-1',
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '08123456789',
            'transaction_date' => date('Y-m-d'),
            'total_rupiah' => 50000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'work-item-1',
            'note_id' => 'note-edit-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 50000,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => 'work-item-1',
            'service_name' => 'Servis Karburator',
            'service_price_rupiah' => 50000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        $response = $this->actingAs($user)
            ->get(route('cashier.notes.workspace.edit', ['noteId' => 'note-edit-1']));

        $response->assertOk();
        $response->assertSee('Edit Nota');
        $response->assertSee('Budi Santoso');
        $response->assertSee('08123456789');
        $response->assertSee('Proses Nota');
        $response->assertSee('id="workspace-payment-modal"', false);
        $response->assertSee('Bayar Penuh');
        $response->assertSee('Bayar Sebagian');
        $response->assertDontSee('Refund');
    }
}
