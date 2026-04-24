<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class CashierRefundRejectsOpenLineFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_refund_allows_operationally_open_line_selection_under_a1_contract(): void
    {
        $user = $this->seedKasir();
        $this->seedOpenPartialPaidNote();

        $response = $this->from(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->actingAs($user)
            ->post(route('cashier.notes.refunds.store', ['noteId' => 'note-1']), [
                'selected_row_ids' => ['wi-1'],
                'refunded_at' => date('Y-m-d'),
                'reason' => 'Refund line open operasional',
            ]);

        $response->assertRedirect(route('cashier.notes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('refund_component_allocations', [
            'note_id' => 'note-1',
            'component_ref_id' => 'wi-1',
        ]);
    }

    private function seedKasir(): User
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Refund Open',
            'email' => 'kasir-refund-open@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        return $user;
    }

    private function seedOpenPartialPaidNote(): void
    {
        $today = date('Y-m-d');

        $this->seedNoteBase('note-1', 'Budi', $today, 20000, 'open');
        $this->seedWorkItemBase('wi-1', 'note-1', 1, WorkItem::TYPE_SERVICE_ONLY, WorkItem::STATUS_OPEN, 20000);
        $this->seedServiceDetailBase('wi-1', 'Servis Open', 20000, ServiceDetail::PART_SOURCE_NONE);
        $this->seedCustomerPaymentBase('payment-1', 10000, $today);
        $this->seedPaymentAllocationBase('allocation-1', 'payment-1', 'note-1', 10000);

        DB::table('payment_component_allocations')->insert([
            'id' => 'pca-1',
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'work_item_id' => 'wi-1',
            'component_type' => 'service_fee',
            'component_ref_id' => 'wi-1',
            'component_amount_rupiah_snapshot' => 20000,
            'allocated_amount_rupiah' => 10000,
            'allocation_priority' => 1,
        ]);
    }
}
