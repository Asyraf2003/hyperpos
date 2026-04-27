<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AdminDueNoteReminderPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_read_due_note_reminder_page(): void
    {
        $this->loginAsAuthorizedAdmin();

        DB::table('notes')->insert([
            'id' => 'note-due-1',
            'customer_name' => 'Budi Reminder',
            'customer_phone' => '081234567890',
            'transaction_date' => '2026-03-30',
            'due_date' => '2026-04-30',
            'total_rupiah' => 200000,
            'note_state' => 'open',
        ]);

        DB::table('note_history_projection')->insert([
            'note_id' => 'note-due-1',
            'transaction_date' => '2026-03-30',
            'note_state' => 'open',
            'customer_name' => 'Budi Reminder',
            'customer_name_normalized' => 'budi reminder',
            'customer_phone' => '081234567890',
            'total_rupiah' => 200000,
            'allocated_rupiah' => 50000,
            'refunded_rupiah' => 0,
            'net_paid_rupiah' => 50000,
            'outstanding_rupiah' => 150000,
            'line_open_count' => 1,
            'line_close_count' => 0,
            'line_refund_count' => 0,
            'has_open_lines' => true,
            'has_close_lines' => false,
            'has_refund_lines' => false,
            'projected_at' => '2026-04-25 10:00:00',
        ]);

        $response = $this->get(route('admin.due-note-reminders.index', ['today' => '2026-04-25']));

        $response->assertOk();
        $response->assertSee('Reminder Jatuh Tempo Nota');
        $response->assertSee('Budi Reminder');
        $response->assertSee('081234567890');
        $response->assertSee('2026-03-30');
        $response->assertSee('2026-04-30');
        $response->assertSee('Rp 150.000');
        $response->assertSee('0 hari');
        $response->assertSee(route('admin.notes.show', ['noteId' => 'note-due-1']), false);
        $response->assertSee('Tidak ada aksi edit atau hapus', false);
        $response->assertSee('Aktifkan Notifikasi', false);
        $response->assertSee('Matikan Notifikasi', false);
        $response->assertSee('data-push-enable-button', false);
        $response->assertSee('data-push-disable-button', false);
        $response->assertDontSee('btn-danger', false);
    }

    public function test_authorized_admin_can_read_overdue_note_days(): void
    {
        $this->loginAsAuthorizedAdmin();

        DB::table('notes')->insert([
            'id' => 'note-overdue-1',
            'customer_name' => 'Sari Overdue',
            'customer_phone' => null,
            'transaction_date' => '2026-03-20',
            'due_date' => '2026-04-20',
            'total_rupiah' => 125000,
            'note_state' => 'open',
        ]);

        DB::table('note_history_projection')->insert([
            'note_id' => 'note-overdue-1',
            'transaction_date' => '2026-03-20',
            'note_state' => 'open',
            'customer_name' => 'Sari Overdue',
            'customer_name_normalized' => 'sari overdue',
            'customer_phone' => null,
            'total_rupiah' => 125000,
            'allocated_rupiah' => 0,
            'refunded_rupiah' => 0,
            'net_paid_rupiah' => 0,
            'outstanding_rupiah' => 125000,
            'line_open_count' => 1,
            'line_close_count' => 0,
            'line_refund_count' => 0,
            'has_open_lines' => true,
            'has_close_lines' => false,
            'has_refund_lines' => false,
            'projected_at' => '2026-04-25 10:00:00',
        ]);

        $response = $this->get(route('admin.due-note-reminders.index', ['today' => '2026-04-25']));

        $response->assertOk();
        $response->assertSee('Sari Overdue');
        $response->assertSee('2026-04-20');
        $response->assertSee('Rp 125.000');
        $response->assertSee('5 hari');
    }
}
