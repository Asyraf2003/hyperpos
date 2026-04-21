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

final class LegacyAllocatedNoteDetailFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_legacy_paid_note_is_rendered_as_close_in_workspace_detail(): void
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Legacy Detail',
            'email' => 'legacy-detail@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        $today = date('Y-m-d');

        $this->seedNoteBase('note-legacy', 'Budi', $today, 50000, 'open');
        $this->seedWorkItemBase('wi-1', 'note-legacy', 1, WorkItem::TYPE_SERVICE_ONLY, WorkItem::STATUS_OPEN, 50000);
        $this->seedServiceDetailBase('wi-1', 'Servis A', 50000, ServiceDetail::PART_SOURCE_NONE);

        $this->seedCustomerPaymentBase('payment-1', 50000, $today);
        $this->seedPaymentAllocationBase('allocation-1', 'payment-1', 'note-legacy', 50000);

        $this->actingAs($user)
            ->get(route('cashier.notes.show', ['noteId' => 'note-legacy']))
            ->assertOk()
            ->assertSee('1 Close')
            ->assertSee('Buka Modal Refund')
            ->assertSee('Refund Nota')
            ->assertDontSee('Refund Line Close Terpilih')
            ->assertDontSee('Pembayaran Line Open Terpilih');
    }
}
