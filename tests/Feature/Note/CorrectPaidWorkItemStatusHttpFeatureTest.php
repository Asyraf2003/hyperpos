<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CorrectPaidWorkItemStatusHttpFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_correct_paid_work_item_status_via_http(): void
    {
        $this->loginAsKasir();
        $user = $this->createCashierUser('cashier@example.test');
        $this->seedPaidServiceOnlyNote('note-1', 'wi-1', 50000);

        $response = $this->actingAs($user)->post('/cashier/notes/note-1/corrections/status', [
            'line_no' => 1,
            'target_status' => WorkItem::STATUS_DONE,
            'reason' => 'Servis sudah selesai.',
        ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-1',
            'status' => WorkItem::STATUS_DONE,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'paid_work_item_status_corrected',
        ]);
    }

    public function test_cashier_cannot_correct_paid_work_item_status_with_invalid_target_status(): void
    {
        $this->loginAsKasir();
        $user = $this->createCashierUser('cashier-invalid-status@example.test');
        $this->seedPaidServiceOnlyNote('note-1', 'wi-1', 50000);

        $response = $this->from(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->actingAs($user)
            ->post('/cashier/notes/note-1/corrections/status', [
                'line_no' => 1,
                'target_status' => 'paid',
                'reason' => 'Status tidak valid.',
            ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $response->assertSessionHasErrors(['target_status']);

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-1',
            'status' => WorkItem::STATUS_OPEN,
        ]);
        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'paid_work_item_status_corrected',
        ]);
    }

    public function test_cashier_cannot_correct_paid_work_item_status_with_blank_reason(): void
    {
        $this->loginAsKasir();
        $user = $this->createCashierUser('cashier-blank-reason@example.test');
        $this->seedPaidServiceOnlyNote('note-1', 'wi-1', 50000);

        $response = $this->from(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->actingAs($user)
            ->post('/cashier/notes/note-1/corrections/status', [
                'line_no' => 1,
                'target_status' => WorkItem::STATUS_DONE,
                'reason' => '   ',
            ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $response->assertSessionHasErrors(['reason']);

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-1',
            'status' => WorkItem::STATUS_OPEN,
        ]);
        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'paid_work_item_status_corrected',
        ]);
    }

    public function test_cashier_cannot_correct_unpaid_work_item_status_via_http(): void
    {
        $this->loginAsKasir();
        $user = $this->createCashierUser('cashier-unpaid-note@example.test');
        $this->seedUnpaidServiceOnlyNote('note-1', 'wi-1', 50000);

        $response = $this->from(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->actingAs($user)
            ->post('/cashier/notes/note-1/corrections/status', [
                'line_no' => 1,
                'target_status' => WorkItem::STATUS_DONE,
                'reason' => 'Harusnya tidak boleh untuk note unpaid.',
            ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $response->assertSessionHasErrors(['correction']);

        $this->assertDatabaseHas('work_items', [
            'id' => 'wi-1',
            'status' => WorkItem::STATUS_OPEN,
        ]);
        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'paid_work_item_status_corrected',
        ]);
    }

    private function createCashierUser(string $email): User
    {
        $user = User::query()->create([
            'name' => 'Kasir',
            'email' => $email,
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        return $user;
    }

    private function seedPaidServiceOnlyNote(string $noteId, string $workItemId, int $subtotalRupiah): void
    {
        DB::table('notes')->insert([
            'id' => $noteId,
            'customer_name' => 'Budi',
            'transaction_date' => '2026-03-14',
            'total_rupiah' => $subtotalRupiah,
        ]);

        DB::table('work_items')->insert([
            'id' => $workItemId,
            'note_id' => $noteId,
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => $subtotalRupiah,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => $workItemId,
            'service_name' => 'Servis A',
            'service_price_rupiah' => $subtotalRupiah,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'cp-1',
            'amount_rupiah' => $subtotalRupiah,
            'paid_at' => '2026-03-14',
        ]);

        DB::table('payment_allocations')->insert([
            'id' => 'pa-1',
            'customer_payment_id' => 'cp-1',
            'note_id' => $noteId,
            'amount_rupiah' => $subtotalRupiah,
        ]);
    }

    private function seedUnpaidServiceOnlyNote(string $noteId, string $workItemId, int $subtotalRupiah): void
    {
        DB::table('notes')->insert([
            'id' => $noteId,
            'customer_name' => 'Budi',
            'transaction_date' => '2026-03-14',
            'total_rupiah' => $subtotalRupiah,
        ]);

        DB::table('work_items')->insert([
            'id' => $workItemId,
            'note_id' => $noteId,
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => $subtotalRupiah,
        ]);

        DB::table('work_item_service_details')->insert([
            'work_item_id' => $workItemId,
            'service_name' => 'Servis A',
            'service_price_rupiah' => $subtotalRupiah,
            'part_source' => ServiceDetail::PART_SOURCE_NONE,
        ]);
    }
}
