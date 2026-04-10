<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetOperationalProfitSummaryHandler;
use App\Application\Shared\DTO\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetOperationalProfitSummaryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_operational_profit_summary_handler_returns_exact_period_profit_row(): void
    {
        $this->seedEmployee('11111111-1111-1111-1111-111111111111', 'Montir A');
        $this->seedExpenseCategory('expense-category-1', 'LISTRIK', 'Listrik');

        $this->seedProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 100, 50000);
        $this->seedProduct('product-2', 'KB-002', 'Kampas Rem', 'Federal', 90, 100000);
        $this->seedProduct('product-3', 'KB-003', 'Produk Scope', 'Federal', 80, 999999);

        DB::table('notes')->insert([
            ['id' => 'note-1', 'customer_name' => 'Budi', 'transaction_date' => '2026-03-15', 'total_rupiah' => 200000],
            ['id' => 'note-2', 'customer_name' => 'Siti', 'transaction_date' => '2026-03-16', 'total_rupiah' => 100000],
            ['id' => 'note-3', 'customer_name' => 'Luar Scope', 'transaction_date' => '2026-03-18', 'total_rupiah' => 999999],
        ]);

        DB::table('work_items')->insert([
            ['id' => 'wi-1', 'note_id' => 'note-1', 'line_no' => 1, 'transaction_type' => 'service_with_external_purchase', 'status' => 'open', 'subtotal_rupiah' => 200000],
            ['id' => 'wi-2', 'note_id' => 'note-2', 'line_no' => 1, 'transaction_type' => 'store_stock_sale_only', 'status' => 'open', 'subtotal_rupiah' => 100000],
            ['id' => 'wi-3', 'note_id' => 'note-3', 'line_no' => 1, 'transaction_type' => 'service_with_external_purchase', 'status' => 'open', 'subtotal_rupiah' => 999999],
        ]);

        DB::table('work_item_external_purchase_lines')->insert([
            ['id' => 'epl-1', 'work_item_id' => 'wi-1', 'cost_description' => 'Part luar', 'unit_cost_rupiah' => 50000, 'qty' => 1, 'line_total_rupiah' => 50000],
            ['id' => 'epl-2', 'work_item_id' => 'wi-3', 'cost_description' => 'Luar scope', 'unit_cost_rupiah' => 999999, 'qty' => 1, 'line_total_rupiah' => 999999],
        ]);

        DB::table('customer_payments')->insert([
            ['id' => 'payment-1', 'amount_rupiah' => 200000, 'paid_at' => '2026-03-15'],
            ['id' => 'payment-2', 'amount_rupiah' => 999999, 'paid_at' => '2026-03-18'],
        ]);

        DB::table('customer_refunds')->insert([
            ['id' => 'refund-1', 'customer_payment_id' => 'payment-1', 'note_id' => 'note-1', 'amount_rupiah' => 10000, 'refunded_at' => '2026-03-16 10:00:00', 'reason' => 'Koreksi'],
            ['id' => 'refund-2', 'customer_payment_id' => 'payment-2', 'note_id' => 'note-3', 'amount_rupiah' => 999999, 'refunded_at' => '2026-03-18 10:00:00', 'reason' => 'Luar scope'],
        ]);

        DB::table('inventory_movements')->insert([
            ['id' => 'm1', 'product_id' => 'product-1', 'movement_type' => 'stock_out', 'source_type' => 'work_item_store_stock_line', 'source_id' => 'ssl-1', 'tanggal_mutasi' => '2026-03-16', 'qty_delta' => -2, 'unit_cost_rupiah' => 15000, 'total_cost_rupiah' => -30000],
            ['id' => 'm2', 'product_id' => 'product-2', 'movement_type' => 'stock_out', 'source_type' => 'other_source', 'source_id' => 'x', 'tanggal_mutasi' => '2026-03-16', 'qty_delta' => -1, 'unit_cost_rupiah' => 99999, 'total_cost_rupiah' => -99999],
            ['id' => 'm3', 'product_id' => 'product-3', 'movement_type' => 'stock_out', 'source_type' => 'work_item_store_stock_line', 'source_id' => 'ssl-2', 'tanggal_mutasi' => '2026-03-18', 'qty_delta' => -1, 'unit_cost_rupiah' => 99999, 'total_cost_rupiah' => -99999],
        ]);

        DB::table('operational_expenses')->insert([
            ['id' => 'expense-1', 'category_id' => 'expense-category-1', 'amount_rupiah' => 20000, 'expense_date' => '2026-03-15', 'description' => 'Listrik', 'payment_method' => 'cash', 'reference_no' => null, 'status' => 'posted', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'expense-2', 'category_id' => 'expense-category-1', 'amount_rupiah' => 5000, 'expense_date' => '2026-03-16', 'description' => 'Draft', 'payment_method' => 'cash', 'reference_no' => null, 'status' => 'draft', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'expense-3', 'category_id' => 'expense-category-1', 'amount_rupiah' => 999999, 'expense_date' => '2026-03-18', 'description' => 'Luar scope', 'payment_method' => 'cash', 'reference_no' => null, 'status' => 'posted', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('payroll_disbursements')->insert([
            ['id' => '22222222-2222-2222-2222-222222222222', 'employee_id' => '11111111-1111-1111-1111-111111111111', 'amount' => 40000, 'disbursement_date' => '2026-03-16 12:00:00', 'mode' => 'weekly', 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => '33333333-3333-3333-3333-333333333333', 'employee_id' => '11111111-1111-1111-1111-111111111111', 'amount' => 999999, 'disbursement_date' => '2026-03-18 12:00:00', 'mode' => 'weekly', 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $result = app(GetOperationalProfitSummaryHandler::class)
            ->handle('2026-03-15', '2026-03-16');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());

        $data = $result->data();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('row', $data);

        $this->assertSame([
            'from_date' => '2026-03-15',
            'to_date' => '2026-03-16',
            'gross_revenue_rupiah' => 300000,
            'refunded_rupiah' => 10000,
            'net_revenue_rupiah' => 290000,
            'external_purchase_cost_rupiah' => 50000,
            'store_stock_cogs_rupiah' => 30000,
            'direct_cost_rupiah' => 80000,
            'gross_profit_rupiah' => 210000,
            'operational_expense_rupiah' => 20000,
            'payroll_disbursement_rupiah' => 40000,
            'net_operational_profit_rupiah' => 150000,
        ], $data['row']);
    }

    private function seedEmployee(string $id, string $name): void
    {
        DB::table('employees')->insert([
            'id' => $id,
            'employee_name' => $name,
            'phone' => null,
            'salary_basis_type' => 'weekly',
            'default_salary_amount' => 3000000,
            'employment_status' => 'active',
            'started_at' => null,
            'ended_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedExpenseCategory(string $id, string $code, string $name): void
    {
        DB::table('expense_categories')->insert([
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'description' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedProduct(
        string $id,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        int $hargaJual
    ): void {
        DB::table('products')->insert([
            'id' => $id,
            'kode_barang' => $kodeBarang,
            'nama_barang' => $namaBarang,
            'nama_barang_normalized' => mb_strtolower(trim($namaBarang)),
            'merek' => $merek,
            'merek_normalized' => mb_strtolower(trim($merek)),
            'ukuran' => $ukuran,
            'harga_jual' => $hargaJual,
            'deleted_at' => null,
            'deleted_by_actor_id' => null,
            'delete_reason' => null,
        ]);
    }
}
