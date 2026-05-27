<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Adapters\Out\Persistence\Eloquent\IdentityAccess\EloquentUser as User;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ClosedPaidNoteEditPaymentSettlementPreviewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_closed_paid_edit_workspace_still_renders_zero_payable_backend_settlement_context(): void
    {
        $admin = $this->adminUser();

        $this->seedClosedPaidServiceOnlyNote(
            noteId: 'note-closed-paid-edit-preview-001',
            revisionId: 'note-closed-paid-edit-preview-001-r001',
            workItemId: 'wi-closed-paid-edit-preview-001',
            paymentId: 'payment-closed-paid-edit-preview-001',
            allocationId: 'allocation-closed-paid-edit-preview-001',
            transactionDate: '2026-05-20',
            totalRupiah: 100000,
        );

        $response = $this->actingAs($admin)
            ->get(route('admin.notes.workspace.edit', [
                'noteId' => 'note-closed-paid-edit-preview-001',
            ]));

        $response->assertOk();
        $response->assertSee('Edit Nota');
        $response->assertSee('Proses Nota');
        $response->assertSee('id="workspace-payment-modal"', false);

        $response->assertSee('Settlement pembayaran backend');
        $response->assertSee('Gross total: 100.000');
        $response->assertSee('Net paid: 100.000');
        $response->assertSee('Payable now: 0');

        $response->assertSee('data-backend-payable-rupiah="0"', false);
        $response->assertSee('data-backend-payment-basis="backend_outstanding_settlement"', false);
    }

    private function adminUser(): User
    {
        $user = User::query()->create([
            'name' => 'Admin Closed Paid Edit Preview',
            'email' => 'admin-closed-paid-edit-preview@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'admin',
        ]);

        return $user;
    }

    private function seedClosedPaidServiceOnlyNote(
        string $noteId,
        string $revisionId,
        string $workItemId,
        string $paymentId,
        string $allocationId,
        string $transactionDate,
        int $totalRupiah
    ): void {
        DB::table('notes')->insert([
            'id' => $noteId,
            'current_revision_id' => $revisionId,
            'latest_revision_number' => 1,
            'customer_name' => 'Budi Closed Paid Edit',
            'customer_phone' => '08123456789',
            'transaction_date' => $transactionDate,
            'note_state' => 'closed',
            'closed_at' => $transactionDate . ' 10:00:00',
            'closed_by_actor_id' => 'admin-seed',
            'reopened_at' => null,
            'reopened_by_actor_id' => null,
            'total_rupiah' => $totalRupiah,
        ]);

        DB::table('work_items')->insert([
            'id' => $workItemId,
            'note_id' => $noteId,
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => $totalRupiah,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => $workItemId,
            'service_name' => 'Servis Closed Paid Edit',
            'service_price_rupiah' => $totalRupiah,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        DB::table('note_revisions')->insert([
            'id' => $revisionId,
            'note_root_id' => $noteId,
            'revision_number' => 1,
            'parent_revision_id' => null,
            'created_by_actor_id' => null,
            'reason' => 'closed paid edit preview fixture',
            'customer_name' => 'Budi Closed Paid Edit',
            'customer_phone' => '08123456789',
            'transaction_date' => $transactionDate,
            'grand_total_rupiah' => $totalRupiah,
            'line_count' => 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => null,
        ]);

        DB::table('note_revision_lines')->insert([
            'id' => $revisionId . '-l001',
            'note_revision_id' => $revisionId,
            'work_item_root_id' => $workItemId,
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'service_label' => 'Servis Closed Paid Edit',
            'service_price_rupiah' => $totalRupiah,
            'subtotal_rupiah' => $totalRupiah,
            'payload' => null,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => null,
        ]);

        DB::table('customer_payments')->insert([
            'id' => $paymentId,
            'amount_rupiah' => $totalRupiah,
            'paid_at' => $transactionDate,
            'payment_method' => 'cash',
        ]);

        DB::table('payment_allocations')->insert([
            'id' => $allocationId,
            'customer_payment_id' => $paymentId,
            'note_id' => $noteId,
            'amount_rupiah' => $totalRupiah,
        ]);
    }
}
