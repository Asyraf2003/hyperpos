<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TransactionCashLedgerPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_transaction_cash_ledger_page(): void
    {
        $this->get(route('admin.reports.transaction_cash_ledger.index'))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_transaction_cash_ledger_page(): void
    {
        $response = $this->actingAs($this->user('kasir'))
            ->get(route('admin.reports.transaction_cash_ledger.index'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_transaction_cash_ledger_page_and_see_report_data(): void
    {
        $this->seedNote('note-1', 'Budi', '2026-04-02', 26000);
        $this->seedNote('note-2', 'Sari', '2026-04-03', 10000);

        $this->seedWorkItem('wi-1', 'note-1', 1, 5000);
        $this->seedWorkItem('wi-2', 'note-1', 2, 3000);
        $this->seedWorkItem('wi-3', 'note-2', 1, 10000);

        $this->seedCustomerPayment('pay-1', 8000, '2026-04-02');
        $this->seedCustomerPayment('pay-2', 4000, '2026-04-03');
        $this->seedCustomerRefund('ref-1', 'pay-1', 'note-1', 1000, '2026-04-04', 'Refund');

        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'p1',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-1',
                'component_type' => 'product_only_work_item',
                'component_ref_id' => 'wi-1',
                'component_amount_rupiah_snapshot' => 5000,
                'allocated_amount_rupiah' => 5000,
                'allocation_priority' => 1,
            ],
            [
                'id' => 'p2',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-2',
                'component_type' => 'service_store_stock_part',
                'component_ref_id' => 'sto-2',
                'component_amount_rupiah_snapshot' => 3000,
                'allocated_amount_rupiah' => 3000,
                'allocation_priority' => 2,
            ],
            [
                'id' => 'p3',
                'customer_payment_id' => 'pay-2',
                'note_id' => 'note-2',
                'work_item_id' => 'wi-3',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-3',
                'component_amount_rupiah_snapshot' => 10000,
                'allocated_amount_rupiah' => 4000,
                'allocation_priority' => 1,
            ],
        ]);

        DB::table('refund_component_allocations')->insert([
            [
                'id' => 'r1',
                'customer_refund_id' => 'ref-1',
                'customer_payment_id' => 'pay-1',
                'note_id' => 'note-1',
                'work_item_id' => 'wi-2',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-2',
                'refunded_amount_rupiah' => 1000,
                'refund_priority' => 1,
            ],
        ]);

        $response = $this->actingAs($this->user('admin'))->get(
            route('admin.reports.transaction_cash_ledger.index', [
                'period_mode' => 'custom',
                'date_from' => '2026-04-01',
                'date_to' => '2026-04-30',
            ])
        );

        $response->assertOk();
        $response->assertSee('Arus Kas Transaksi');
        $response->assertSee('transaction-cash-ledger-filter-form', false);
        $response->assertSee('2026-04-02');
        $response->assertSee('2026-04-03');
        $response->assertSee('2026-04-04');
        $response->assertSee('note-1');
        $response->assertSee('note-2');
        $response->assertSee('payment_allocation');
        $response->assertSee('refund');
        $response->assertSee('Rp 12.000');
        $response->assertSee('Rp 1.000');
        $response->assertSee('Rp 11.000');
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-reporting-page@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }

    private function seedNote(string $id, string $customerName, string $transactionDate, int $totalRupiah): void
    {
        DB::table('notes')->insert([
            'id' => $id,
            'customer_name' => $customerName,
            'transaction_date' => $transactionDate,
            'total_rupiah' => $totalRupiah,
        ]);
    }

    private function seedWorkItem(string $id, string $noteId, int $lineNo, int $subtotalRupiah): void
    {
        DB::table('work_items')->insert([
            'id' => $id,
            'note_id' => $noteId,
            'line_no' => $lineNo,
            'transaction_type' => 'service_only',
            'status' => 'open',
            'subtotal_rupiah' => $subtotalRupiah,
        ]);
    }

    private function seedCustomerPayment(string $id, int $amountRupiah, string $paidAt): void
    {
        DB::table('customer_payments')->insert([
            'id' => $id,
            'amount_rupiah' => $amountRupiah,
            'paid_at' => $paidAt,
        ]);
    }

    private function seedCustomerRefund(
        string $id,
        string $paymentId,
        string $noteId,
        int $amountRupiah,
        string $refundedAt,
        string $reason
    ): void {
        DB::table('customer_refunds')->insert([
            'id' => $id,
            'customer_payment_id' => $paymentId,
            'note_id' => $noteId,
            'amount_rupiah' => $amountRupiah,
            'refunded_at' => $refundedAt,
            'reason' => $reason,
        ]);
    }
}
