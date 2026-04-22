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

final class CashierNoteRevisionSubmitFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_workspace_update_route_creates_new_revision_instead_of_overwriting_root_identity(): void
    {
        $user = $this->seedKasir();
        $this->seedOpenServiceOnlyNote();

        $this->actingAs($user)->get(route('cashier.notes.show', ['noteId' => 'note-1']))->assertOk();

        $response = $this->actingAs($user)->patch(route('cashier.notes.workspace.update', ['noteId' => 'note-1']), [
            'note' => [
                'customer_name' => 'Budi Revised',
                'customer_phone' => '08123',
                'transaction_date' => date('Y-m-d'),
            ],
            'items' => [
                [
                    'entry_mode' => 'service',
                    'description' => null,
                    'part_source' => 'none',
                    'service' => [
                        'name' => 'Servis Revised',
                        'price_rupiah' => '75000',
                        'notes' => null,
                    ],
                    'product_lines' => [],
                    'external_purchase_lines' => [],
                ],
            ],
            'inline_payment' => [
                'decision' => 'skip',
            ],
        ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));

        $this->assertDatabaseHas('notes', [
            'id' => 'note-1',
            'current_revision_id' => 'note-1-r002',
            'latest_revision_number' => 2,
        ]);

        $this->assertDatabaseHas('note_revisions', [
            'id' => 'note-1-r002',
            'note_root_id' => 'note-1',
            'revision_number' => 2,
            'customer_name' => 'Budi Revised',
        ]);
    }

    private function seedKasir(): User
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Revision Submit',
            'email' => 'kasir-revision-submit@example.test',
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        return $user;
    }

    private function seedOpenServiceOnlyNote(): void
    {
        $today = date('Y-m-d');

        $this->seedNoteBase('note-1', 'Budi', $today, 50000, 'open');
        $this->seedWorkItemBase('wi-1', 'note-1', 1, WorkItem::TYPE_SERVICE_ONLY, WorkItem::STATUS_OPEN, 50000);
        $this->seedServiceDetailBase('wi-1', 'Servis Lama', 50000, ServiceDetail::PART_SOURCE_NONE);
    }
}
