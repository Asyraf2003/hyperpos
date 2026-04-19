<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CashierProtectedNoteRoutesAccessGuardFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_open_detail_for_closed_note_within_date_window(): void
    {
        $user = $this->seedKasir();
        $this->seedServiceOnlyNote('note-closed', date('Y-m-d'), 'closed');

        $this->actingAs($user)
            ->get(route('cashier.notes.show', ['noteId' => 'note-closed']))
            ->assertOk();
    }

    public function test_cashier_cannot_open_workspace_edit_for_closed_note(): void
    {
        $user = $this->seedKasir();
        $this->seedServiceOnlyNote('note-closed', date('Y-m-d'), 'closed');

        $this->actingAs($user)
            ->get(route('cashier.notes.workspace.edit', ['noteId' => 'note-closed']))
            ->assertForbidden();
    }

    public function test_cashier_cannot_view_note_older_than_two_days_even_if_closed(): void
    {
        $user = $this->seedKasir();
        $oldDate = date('Y-m-d', strtotime('-2 day'));
        $this->seedServiceOnlyNote('note-old-closed', $oldDate, 'closed');

        $this->actingAs($user)
            ->get(route('cashier.notes.show', ['noteId' => 'note-old-closed']))
            ->assertForbidden();
    }

    public function test_cashier_cannot_post_payment_for_open_note_older_than_two_days(): void
    {
        $user = $this->seedKasir();
        $oldDate = date('Y-m-d', strtotime('-2 day'));
        $this->seedServiceOnlyNote('note-old', $oldDate, 'open');

        $this->actingAs($user)
            ->post(route('cashier.notes.payments.store', ['noteId' => 'note-old']), [
                'selected_row_ids' => ['wi-note-old'],
                'payment_method' => 'cash',
                'paid_at' => $oldDate,
                'amount_received' => 50000,
            ])
            ->assertForbidden();
    }

    private function seedKasir(): User
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Protected Route',
            'email' => 'cashier-protected-route@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        return $user;
    }

    private function seedServiceOnlyNote(string $noteId, string $transactionDate, string $noteState): void
    {
        DB::table('notes')->insert([
            'id' => $noteId,
            'customer_name' => 'Budi',
            'transaction_date' => $transactionDate,
            'note_state' => $noteState,
            'total_rupiah' => 50000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'wi-' . $noteId,
            'note_id' => $noteId,
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 50000,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => 'wi-' . $noteId,
            'service_name' => 'Servis A',
            'service_price_rupiah' => 50000,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);
    }
}
