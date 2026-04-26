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

    public function test_cashier_edit_workspace_repairs_existing_revision_pointer_when_current_pointer_is_empty(): void
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Edit Pointer Repair',
            'email' => 'edit-pointer-repair@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        DB::table('notes')->insert([
            'id' => 'note-edit-pointer-1',
            'customer_name' => 'Budi Pointer',
            'customer_phone' => '08123456000',
            'transaction_date' => date('Y-m-d'),
            'total_rupiah' => 50000,
            'current_revision_id' => null,
            'latest_revision_number' => 0,
        ]);

        DB::table('work_items')->insert([
            'id' => 'work-item-edit-pointer-1',
            'note_id' => 'note-edit-pointer-1',
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 50000,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => 'work-item-edit-pointer-1',
            'service_name' => 'Servis Pointer',
            'service_price_rupiah' => 50000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        DB::table('note_revisions')->insert([
            'id' => 'note-edit-pointer-1-r001',
            'note_root_id' => 'note-edit-pointer-1',
            'revision_number' => 1,
            'parent_revision_id' => null,
            'created_by_actor_id' => null,
            'reason' => 'legacy edit revision without pointer',
            'customer_name' => 'Budi Pointer',
            'customer_phone' => '08123456000',
            'transaction_date' => date('Y-m-d'),
            'grand_total_rupiah' => 50000,
            'line_count' => 0,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get(route('cashier.notes.workspace.edit', ['noteId' => 'note-edit-pointer-1']));

        $response->assertOk();
        $response->assertSee('Edit Nota');
        $response->assertSee('Budi Pointer');

        $this->assertDatabaseHas('notes', [
            'id' => 'note-edit-pointer-1',
            'current_revision_id' => 'note-edit-pointer-1-r001',
            'latest_revision_number' => 1,
        ]);
    }

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
