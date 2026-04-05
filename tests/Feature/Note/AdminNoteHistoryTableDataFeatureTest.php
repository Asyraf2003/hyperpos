<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Core\Note\WorkItem\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AdminNoteHistoryTableDataFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_fetch_note_history_table_items(): void
    {
        $this->loginAsAuthorizedAdmin();
        $today = now()->toDateString();

        $this->seedOpenUnpaidNote('note-open', $today, 'Budi', '08123');
        $this->seedClosedPaidNote('note-closed', $today, 'Andi', '08234');

        $response = $this->getJson(route('admin.notes.table', [
            'date_from' => $today,
            'date_to' => $today,
        ]));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.pagination.total', 2);

        /** @var Collection<string, array<string, mixed>> $items */
        $items = collect($response->json('data.items'))->keyBy('note_id');

        $this->assertSame('Belum Dibayar', $items->get('note-open')['payment_status_label']);
        $this->assertSame('Editable Normal', $items->get('note-open')['editability_label']);
        $this->assertSame('Open: 1 • Selesai: 0 • Batal: 0', $items->get('note-open')['work_status_label']);
        $this->assertStringContainsString('/admin/notes/note-open', (string) $items->get('note-open')['action_url']);

        $this->assertSame('Lunas', $items->get('note-closed')['payment_status_label']);
        $this->assertSame('Admin Ketat', $items->get('note-closed')['editability_label']);
        $this->assertSame('Open: 0 • Selesai: 1 • Batal: 0', $items->get('note-closed')['work_status_label']);
        $this->assertStringContainsString('/admin/notes/note-closed', (string) $items->get('note-closed')['action_url']);
    }

    public function test_authorized_admin_can_filter_note_history_by_editability(): void
    {
        $this->loginAsAuthorizedAdmin();
        $today = now()->toDateString();

        $this->seedOpenUnpaidNote('note-open', $today, 'Budi', '08123');
        $this->seedClosedPaidNote('note-closed', $today, 'Andi', '08234');

        $response = $this->getJson(route('admin.notes.table', [
            'date_from' => $today,
            'date_to' => $today,
            'editability' => 'admin_strict',
        ]));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.pagination.total', 1);
        $response->assertJsonPath('data.items.0.note_id', 'note-closed');
        $response->assertJsonPath('data.items.0.editability_label', 'Admin Ketat');
    }

    private function seedOpenUnpaidNote(string $noteId, string $date, string $customerName, string $phone): void
    {
        DB::table('notes')->insert([
            'id' => $noteId,
            'customer_name' => $customerName,
            'customer_phone' => $phone,
            'transaction_date' => $date,
            'total_rupiah' => 40000,
            'note_state' => 'open',
        ]);

        DB::table('work_items')->insert([
            'id' => $noteId . '-wi-1',
            'note_id' => $noteId,
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_OPEN,
            'subtotal_rupiah' => 40000,
        ]);
    }

    private function seedClosedPaidNote(string $noteId, string $date, string $customerName, string $phone): void
    {
        DB::table('notes')->insert([
            'id' => $noteId,
            'customer_name' => $customerName,
            'customer_phone' => $phone,
            'transaction_date' => $date,
            'total_rupiah' => 50000,
            'note_state' => 'closed',
        ]);

        DB::table('work_items')->insert([
            'id' => $noteId . '-wi-1',
            'note_id' => $noteId,
            'line_no' => 1,
            'transaction_type' => WorkItem::TYPE_SERVICE_ONLY,
            'status' => WorkItem::STATUS_DONE,
            'subtotal_rupiah' => 50000,
        ]);

        DB::table('customer_payments')->insert([
            'id' => $noteId . '-pay-1',
            'amount_rupiah' => 50000,
            'paid_at' => $date,
        ]);

        DB::table('payment_allocations')->insert([
            'id' => $noteId . '-alloc-1',
            'customer_payment_id' => $noteId . '-pay-1',
            'note_id' => $noteId,
            'amount_rupiah' => 50000,
        ]);
    }
}
