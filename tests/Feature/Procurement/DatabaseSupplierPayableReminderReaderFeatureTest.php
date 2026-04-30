<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Ports\Out\Procurement\SupplierPayableReminderReaderPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DatabaseSupplierPayableReminderReaderFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_due_outstanding_non_voided_supplier_invoices_only(): void
    {
        $this->invoice('overdue', '2026-04-20', 100000, 'CV Overdue');
        $this->payment('payment-overdue', 'invoice-overdue', 20000);

        $this->invoice('due-today', '2026-04-25', 200000, 'CV Due Today');

        $this->invoice('reversed', '2026-04-28', 120000, 'CV Reversed');
        $this->payment('payment-reversed', 'invoice-reversed', 120000);
        $this->reversal('reversal-reversed', 'payment-reversed');

        $this->invoice('h5', '2026-04-30', 150000, 'CV H5');
        $this->payment('payment-h5', 'invoice-h5', 50000);

        $this->invoice('h6', '2026-05-01', 100000, 'CV H6');

        $this->invoice('paid-full', '2026-04-30', 100000, 'CV Paid Full');
        $this->payment('payment-paid-full', 'invoice-paid-full', 100000);

        $this->invoice('voided', '2026-04-22', 100000, 'CV Voided', '2026-04-23 10:00:00');

        $rows = app(SupplierPayableReminderReaderPort::class)->findDueReminders('2026-04-25');

        self::assertSame([
            'invoice-overdue',
            'invoice-due-today',
            'invoice-reversed',
            'invoice-h5',
        ], array_map(static fn ($row): string => $row->supplierInvoiceId, $rows));

        self::assertSame([5, 0, 0, 0], array_map(static fn ($row): int => $row->daysOverdue, $rows));
        self::assertSame([80000, 200000, 120000, 100000], array_map(
            static fn ($row): int => $row->outstandingRupiah,
            $rows,
        ));
    }

    private function invoice(
        string $key,
        string $dueDate,
        int $grandTotalRupiah,
        string $supplierName,
        ?string $voidedAt = null,
    ): void {
        $invoiceId = 'invoice-'.$key;
        $supplierId = 'supplier-'.$key;

        DB::table('suppliers')->insert([
            'id' => $supplierId,
            'nama_pt_pengirim' => $supplierName,
            'nama_pt_pengirim_normalized' => mb_strtolower($supplierName, 'UTF-8'),
        ]);

        DB::table('supplier_invoices')->insert([
            'id' => $invoiceId,
            'supplier_id' => $supplierId,
            'supplier_nama_pt_pengirim_snapshot' => $supplierName,
            'nomor_faktur' => 'NF-'.$key,
            'nomor_faktur_normalized' => 'nf-'.$key,
            'document_kind' => 'invoice',
            'lifecycle_status' => 'active',
            'tanggal_pengiriman' => '2026-04-01',
            'jatuh_tempo' => $dueDate,
            'grand_total_rupiah' => $grandTotalRupiah,
            'voided_at' => $voidedAt,
            'void_reason' => $voidedAt === null ? null : 'voided for reminder test',
            'last_revision_no' => 0,
        ]);
    }

    private function payment(string $paymentId, string $invoiceId, int $amountRupiah): void
    {
        DB::table('supplier_payments')->insert([
            'id' => $paymentId,
            'supplier_invoice_id' => $invoiceId,
            'amount_rupiah' => $amountRupiah,
            'paid_at' => '2026-04-10',
            'proof_status' => 'uploaded',
        ]);
    }

    private function reversal(string $reversalId, string $paymentId): void
    {
        DB::table('supplier_payment_reversals')->insert([
            'id' => $reversalId,
            'supplier_payment_id' => $paymentId,
            'reason' => 'reversed for reminder test',
            'performed_by_actor_id' => 'actor-test',
            'created_at' => '2026-04-11 10:00:00',
            'updated_at' => '2026-04-11 10:00:00',
        ]);
    }
}
