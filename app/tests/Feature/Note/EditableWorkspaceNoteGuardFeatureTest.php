<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Application\Note\Services\EditableWorkspaceNoteGuard;
use App\Core\Shared\Exceptions\DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class EditableWorkspaceNoteGuardFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_unpaid_note_without_payment_allocation(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'customer_phone' => '08123',
            'transaction_date' => '2026-03-15',
            'total_rupiah' => 50000,
        ]);

        $guard = app(EditableWorkspaceNoteGuard::class);

        $this->expectNotToPerformAssertions();

        $guard->assertEditable('note-1');
    }

    public function test_it_allows_open_note_that_already_has_partial_payment(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'customer_phone' => '08123',
            'transaction_date' => '2026-03-15',
            'total_rupiah' => 50000,
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'payment-1',
            'amount_rupiah' => 20000,
            'paid_at' => '2026-03-15',
        ]);

        DB::table('payment_allocations')->insert([
            'id' => 'allocation-1',
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'amount_rupiah' => 20000,
        ]);

        $guard = app(EditableWorkspaceNoteGuard::class);

        $this->expectNotToPerformAssertions();

        $guard->assertEditable('note-1');
    }

    public function test_it_allows_note_again_when_refund_makes_net_paid_below_total(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'customer_phone' => '08123',
            'transaction_date' => '2026-03-15',
            'total_rupiah' => 50000,
            'note_state' => 'closed',
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'payment-1',
            'amount_rupiah' => 50000,
            'paid_at' => '2026-03-15',
        ]);

        DB::table('payment_allocations')->insert([
            'id' => 'allocation-1',
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'amount_rupiah' => 50000,
        ]);

        DB::table('customer_refunds')->insert([
            'id' => 'refund-1',
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'amount_rupiah' => 10000,
            'refunded_at' => '2026-03-15',
            'reason' => 'Koreksi nominal',
        ]);

        $guard = app(EditableWorkspaceNoteGuard::class);

        $this->expectNotToPerformAssertions();

        $guard->assertEditable('note-1');
    }

    public function test_it_rejects_note_that_is_already_fully_settled(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'customer_phone' => '08123',
            'transaction_date' => '2026-03-15',
            'total_rupiah' => 50000,
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'payment-1',
            'amount_rupiah' => 50000,
            'paid_at' => '2026-03-15',
        ]);

        DB::table('payment_allocations')->insert([
            'id' => 'allocation-1',
            'customer_payment_id' => 'payment-1',
            'note_id' => 'note-1',
            'amount_rupiah' => 50000,
        ]);

        $guard = app(EditableWorkspaceNoteGuard::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Nota close tidak boleh diedit lewat workspace.');

        $guard->assertEditable('note-1');
    }

    public function test_it_rejects_missing_note(): void
    {
        $guard = app(EditableWorkspaceNoteGuard::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Nota tidak ditemukan.');

        $guard->assertEditable('missing-note');
    }
}
