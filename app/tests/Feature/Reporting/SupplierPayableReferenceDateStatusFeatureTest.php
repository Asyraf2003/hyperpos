<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetSupplierPayableSummaryHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SupplierPayableReferenceDateStatusFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_payable_status_uses_reference_date_boundaries(): void
    {
        $this->seedSupplier('supplier-1', 'PT Referensi');
        $this->seedSupplierInvoice('invoice-overdue', 'supplier-1', '2030-01-07', '2030-01-30', 100000);
        $this->seedSupplierInvoice('invoice-due-today', 'supplier-1', '2030-01-08', '2030-01-31', 50000);
        $this->seedSupplierInvoice('invoice-not-due', 'supplier-1', '2030-01-09', '2030-02-01', 30000);
        $this->seedSupplierInvoice('invoice-settled', 'supplier-1', '2030-01-10', '2030-01-15', 20000);

        $this->seedSupplierPayment('payment-overdue', 'invoice-overdue', 20000, '2030-01-07', 'pending');
        $this->seedSupplierPayment('payment-due-today', 'invoice-due-today', 10000, '2030-01-08', 'pending');
        $this->seedSupplierPayment('payment-settled', 'invoice-settled', 20000, '2030-01-10', 'uploaded');

        $result = app(GetSupplierPayableSummaryHandler::class)
            ->handle('2030-01-01', '2030-01-31', '2030-01-31');

        $this->assertTrue($result->isSuccess());

        $rows = $result->data()['rows'];

        $this->assertSame('Lewat Jatuh Tempo', $rows[0]['due_status_label']);
        $this->assertSame('Jatuh Tempo Hari Ini', $rows[1]['due_status_label']);
        $this->assertSame('Belum Jatuh Tempo', $rows[2]['due_status_label']);
        $this->assertSame('Lunas', $rows[3]['due_status_label']);
    }

    private function seedSupplier(string $id, string $namaPtPengirim): void
    {
        DB::table('suppliers')->insert([
            'id' => $id,
            'nama_pt_pengirim' => $namaPtPengirim,
            'nama_pt_pengirim_normalized' => strtolower($namaPtPengirim),
            'deleted_at' => null,
            'deleted_by_actor_id' => null,
            'delete_reason' => null,
        ]);
    }

    private function seedSupplierInvoice(
        string $id,
        string $supplierId,
        string $shipmentDate,
        string $dueDate,
        int $grandTotalRupiah
    ): void {
        DB::table('supplier_invoices')->insert([
            'id' => $id,
            'supplier_id' => $supplierId,
            'supplier_nama_pt_pengirim_snapshot' => DB::table('suppliers')->where('id', $supplierId)->value('nama_pt_pengirim'),
            'tanggal_pengiriman' => $shipmentDate,
            'jatuh_tempo' => $dueDate,
            'grand_total_rupiah' => $grandTotalRupiah,
        ]);
    }

    private function seedSupplierPayment(
        string $id,
        string $supplierInvoiceId,
        int $amountRupiah,
        string $paidAt,
        string $proofStatus
    ): void {
        DB::table('supplier_payments')->insert([
            'id' => $id,
            'supplier_invoice_id' => $supplierInvoiceId,
            'amount_rupiah' => $amountRupiah,
            'paid_at' => $paidAt,
            'proof_status' => $proofStatus,
            'proof_storage_path' => null,
        ]);
    }
}
