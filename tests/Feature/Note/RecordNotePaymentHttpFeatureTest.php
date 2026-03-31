<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RecordNotePaymentHttpFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_cashier_can_record_note_payment_for_selected_rows(): void
    {
        $this->loginAsKasir();
        $user = $this->createCashierUser('cashier-success@example.test');
        $this->seedUnpaidNoteWithTwoRows();

        $response = $this->actingAs($user)->post('/cashier/notes/note-1/payments', [
            'selected_row_ids' => ['wi-1'],
            'payment_method' => 'cash',
            'paid_at' => '2026-03-15',
            'amount_received' => 70000,
        ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $this->assertDatabaseHas('customer_payments', [
            'amount_rupiah' => 50000,
            'paid_at' => '2026-03-15',
        ]);

        $paymentId = (string) DB::table('customer_payments')->value('id');
        $this->assertNotSame('', $paymentId);

        $this->assertDatabaseHas('payment_allocations', [
            'customer_payment_id' => $paymentId,
            'note_id' => 'note-1',
            'amount_rupiah' => 50000,
        ]);
    }

    public function test_authenticated_cashier_cannot_record_cash_payment_without_amount_received(): void
    {
        $this->loginAsKasir();
        $user = $this->createCashierUser('cashier-no-amount@example.test');
        $this->seedUnpaidNoteWithTwoRows();

        $response = $this->from(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->actingAs($user)
            ->post('/cashier/notes/note-1/payments', [
                'selected_row_ids' => ['wi-1'],
                'payment_method' => 'cash',
                'paid_at' => '2026-03-15',
                'amount_received' => null,
            ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $response->assertSessionHasErrors([
            'amount_received' => 'Uang masuk wajib diisi untuk pembayaran cash.',
        ]);

        $this->assertDatabaseCount('customer_payments', 0);
        $this->assertDatabaseCount('payment_allocations', 0);
    }

    public function test_authenticated_cashier_cannot_record_cash_payment_when_amount_received_is_less_than_selected_total(): void
    {
        $this->loginAsKasir();
        $user = $this->createCashierUser('cashier-less-cash@example.test');
        $this->seedUnpaidNoteWithTwoRows();

        $response = $this->from(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->actingAs($user)
            ->post('/cashier/notes/note-1/payments', [
                'selected_row_ids' => ['wi-1', 'wi-2'],
                'payment_method' => 'cash',
                'paid_at' => '2026-03-15',
                'amount_received' => 120000,
            ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $response->assertSessionHasErrors([
            'payment' => 'Uang masuk cash tidak boleh kurang dari total yang dibayar.',
        ]);

        $this->assertDatabaseCount('customer_payments', 0);
        $this->assertDatabaseCount('payment_allocations', 0);
    }

    public function test_authenticated_cashier_cannot_record_note_payment_when_selected_rows_are_empty(): void
    {
        $this->loginAsKasir();
        $user = $this->createCashierUser('cashier-empty-rows@example.test');
        $this->seedUnpaidNoteWithTwoRows();

        $response = $this->from(route('cashier.notes.show', ['noteId' => 'note-1']))
            ->actingAs($user)
            ->post('/cashier/notes/note-1/payments', [
                'selected_row_ids' => [],
                'payment_method' => 'cash',
                'paid_at' => '2026-03-15',
                'amount_received' => 70000,
            ]);

        $response->assertRedirect(route('cashier.notes.show', ['noteId' => 'note-1']));
        $response->assertSessionHasErrors(['selected_row_ids']);

        $this->assertDatabaseCount('customer_payments', 0);
        $this->assertDatabaseCount('payment_allocations', 0);
    }

    private function createCashierUser(string $email): User
    {
        $user = User::query()->create([
            'name' => 'Kasir Aktif',
            'email' => $email,
            'password' => 'password',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'kasir',
        ]);

        return $user;
    }

    private function seedUnpaidNoteWithTwoRows(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'transaction_date' => '2026-03-14',
            'total_rupiah' => 150000,
        ]);

        DB::table('work_items')->insert([
            [
                'id' => 'wi-1',
                'note_id' => 'note-1',
                'line_no' => 1,
                'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
                'status' => WorkItem::STATUS_OPEN,
                'subtotal_rupiah' => 50000,
            ],
            [
                'id' => 'wi-2',
                'note_id' => 'note-1',
                'line_no' => 2,
                'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
                'status' => WorkItem::STATUS_OPEN,
                'subtotal_rupiah' => 100000,
            ],
        ]);

        DB::table('work_item_service_details')->insert([
            [
                'work_item_id' => 'wi-1',
                'service_name' => 'Servis A',
                'service_price_rupiah' => 50000,
                'part_source' => ServiceDetail::PART_SOURCE_NONE,
            ],
            [
                'work_item_id' => 'wi-2',
                'service_name' => 'Servis B',
                'service_price_rupiah' => 100000,
                'part_source' => ServiceDetail::PART_SOURCE_NONE,
            ],
        ]);
    }
}
