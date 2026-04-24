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

final class CashierNoteRevisionSmokeTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;

    public function test_note_detail_smoke_shows_revision_block(): void
    {
        $user = $this->seedKasir();
        $this->seedOpenServiceOnlyNote();

        $response = $this->actingAs($user)->get(route('cashier.notes.show', ['noteId' => 'note-1']));

        $response->assertOk()
            ->assertSee('Versioning Nota')
            ->assertSee('Revision Aktif');

        $this->assertDatabaseHas('note_revisions', [
            'note_root_id' => 'note-1',
        ]);
    }

    private function seedKasir(): User
    {
        $this->loginAsKasir();

        $user = User::query()->create([
            'name' => 'Kasir Revision Smoke',
            'email' => 'kasir-revision-smoke@example.test',
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
