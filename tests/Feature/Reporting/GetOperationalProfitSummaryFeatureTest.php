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

    public function test_get_operational_profit_summary_handler_returns_exact_cash_based_period_profit_row(): void
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
            ['id' => 'expense-1', 'category_id' => 'expense-category-1', 'amount_rupiah' => 20000, 'expense_date' => '2026-03-15', 'description' => 'Listrik', 'payment_method' => 'cash', 'reference_no' => null, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null],
            ['id' => 'expense-2', 'category_id' => 'expense-category-1', 'amount_rupiah' => 5000, 'expense_date' => '2026-03-16', 'description' => 'Expense dihapus', 'payment_method' => 'cash', 'reference_no' => null, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => '2026-03-16 09:00:00'],
            ['id' => 'expense-3', 'category_id' => 'expense-category-1', 'amount_rupiah' => 999999, 'expense_date' => '2026-03-18', 'description' => 'Luar scope', 'payment_method' => 'cash', 'reference_no' => null, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null],
        ]);

        DB::table('payroll_disbursements')->insert([
            ['id' => '22222222-2222-2222-2222-222222222222', 'employee_id' => '11111111-1111-1111-1111-111111111111', 'amount' => 40000, 'disbursement_date' => '2026-03-16 12:00:00', 'mode' => 'weekly', 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => '33333333-3333-3333-3333-333333333333', 'employee_id' => '11111111-1111-1111-1111-111111111111', 'amount' => 999999, 'disbursement_date' => '2026-03-18 12:00:00', 'mode' => 'weekly', 'notes' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('employee_debts')->insert([
            ['id' => 'debt-1', 'employee_id' => '11111111-1111-1111-1111-111111111111', 'total_debt' => 15000, 'remaining_balance' => 10000, 'status' => 'unpaid', 'notes' => 'Kasbon periode', 'created_at' => '2026-03-16 08:00:00', 'updated_at' => '2026-03-16 08:00:00'],
            ['id' => 'debt-2', 'employee_id' => '11111111-1111-1111-1111-111111111111', 'total_debt' => 999999, 'remaining_balance' => 999999, 'status' => 'unpaid', 'notes' => 'Luar scope', 'created_at' => '2026-03-18 08:00:00', 'updated_at' => '2026-03-18 08:00:00'],
        ]);

        DB::table('employee_debt_payments')->insert([
            ['id' => 'debt-payment-1', 'employee_debt_id' => 'debt-1', 'amount' => 5000, 'payment_date' => '2026-03-16 09:00:00', 'notes' => 'Bayar sebagian', 'created_at' => '2026-03-16 09:00:00', 'updated_at' => '2026-03-16 09:00:00'],
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
            'cash_in_rupiah' => 200000,
            'refunded_rupiah' => 10000,
            'external_purchase_cost_rupiah' => 50000,
            'store_stock_cogs_rupiah' => 30000,
            'product_purchase_cost_rupiah' => 80000,
            'operational_expense_rupiah' => 20000,
            'payroll_disbursement_rupiah' => 40000,
            'employee_debt_cash_out_rupiah' => 15000,
            'cash_operational_profit_rupiah' => 35000,
        ], $data['row']);
    }


    public function test_get_operational_profit_summary_handler_nets_fully_refunded_note_product_costs_to_zero(): void
    {
        $this->seedProduct('product-full-refund-1', 'KB-FULL-RFD-001', 'Ban Full Refund', 'Federal', 100, 150000);

        DB::table('notes')->insert([
            'id' => 'note-full-refund-1',
            'customer_name' => 'Budi Full Refund',
            'transaction_date' => '2026-04-02',
            'note_state' => 'refunded',
            'total_rupiah' => 0,
        ]);

        DB::table('work_items')->insert([
            [
                'id' => 'wi-full-refund-stock-1',
                'note_id' => 'note-full-refund-1',
                'line_no' => 1,
                'transaction_type' => 'service_with_store_stock_part',
                'status' => 'canceled',
                'subtotal_rupiah' => 142000,
            ],
            [
                'id' => 'wi-full-refund-external-1',
                'note_id' => 'note-full-refund-1',
                'line_no' => 2,
                'transaction_type' => 'service_with_external_purchase',
                'status' => 'canceled',
                'subtotal_rupiah' => 61000,
            ],
        ]);

        DB::table('work_item_store_stock_lines')->insert([
            'id' => 'ssl-full-refund-1',
            'work_item_id' => 'wi-full-refund-stock-1',
            'product_id' => 'product-full-refund-1',
            'qty' => 1,
            'line_total_rupiah' => 122000,
        ]);

        DB::table('work_item_external_purchase_lines')->insert([
            'id' => 'ext-full-refund-1',
            'work_item_id' => 'wi-full-refund-external-1',
            'cost_description' => 'Beli luar full refund',
            'unit_cost_rupiah' => 21000,
            'qty' => 1,
            'line_total_rupiah' => 21000,
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'payment-full-refund-1',
            'amount_rupiah' => 203000,
            'paid_at' => '2026-04-02',
        ]);

        DB::table('payment_component_allocations')->insert([
            [
                'id' => 'pca-full-refund-1',
                'customer_payment_id' => 'payment-full-refund-1',
                'note_id' => 'note-full-refund-1',
                'work_item_id' => 'wi-full-refund-stock-1',
                'component_type' => 'service_store_stock_part',
                'component_ref_id' => 'ssl-full-refund-1',
                'component_amount_rupiah_snapshot' => 122000,
                'allocated_amount_rupiah' => 122000,
                'allocation_priority' => 1,
            ],
            [
                'id' => 'pca-full-refund-2',
                'customer_payment_id' => 'payment-full-refund-1',
                'note_id' => 'note-full-refund-1',
                'work_item_id' => 'wi-full-refund-stock-1',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-full-refund-stock-1',
                'component_amount_rupiah_snapshot' => 20000,
                'allocated_amount_rupiah' => 20000,
                'allocation_priority' => 2,
            ],
            [
                'id' => 'pca-full-refund-3',
                'customer_payment_id' => 'payment-full-refund-1',
                'note_id' => 'note-full-refund-1',
                'work_item_id' => 'wi-full-refund-external-1',
                'component_type' => 'service_external_purchase_part',
                'component_ref_id' => 'ext-full-refund-1',
                'component_amount_rupiah_snapshot' => 21000,
                'allocated_amount_rupiah' => 21000,
                'allocation_priority' => 3,
            ],
            [
                'id' => 'pca-full-refund-4',
                'customer_payment_id' => 'payment-full-refund-1',
                'note_id' => 'note-full-refund-1',
                'work_item_id' => 'wi-full-refund-external-1',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-full-refund-external-1',
                'component_amount_rupiah_snapshot' => 40000,
                'allocated_amount_rupiah' => 40000,
                'allocation_priority' => 4,
            ],
        ]);

        DB::table('customer_refunds')->insert([
            'id' => 'refund-full-refund-1',
            'customer_payment_id' => 'payment-full-refund-1',
            'note_id' => 'note-full-refund-1',
            'amount_rupiah' => 203000,
            'refunded_at' => '2026-04-02 10:00:00',
            'reason' => 'Refund penuh nota reporting neutrality',
        ]);

        DB::table('refund_component_allocations')->insert([
            [
                'id' => 'rca-full-refund-1',
                'customer_refund_id' => 'refund-full-refund-1',
                'customer_payment_id' => 'payment-full-refund-1',
                'note_id' => 'note-full-refund-1',
                'work_item_id' => 'wi-full-refund-stock-1',
                'component_type' => 'service_store_stock_part',
                'component_ref_id' => 'ssl-full-refund-1',
                'refunded_amount_rupiah' => 122000,
                'refund_priority' => 1,
            ],
            [
                'id' => 'rca-full-refund-2',
                'customer_refund_id' => 'refund-full-refund-1',
                'customer_payment_id' => 'payment-full-refund-1',
                'note_id' => 'note-full-refund-1',
                'work_item_id' => 'wi-full-refund-stock-1',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-full-refund-stock-1',
                'refunded_amount_rupiah' => 20000,
                'refund_priority' => 2,
            ],
            [
                'id' => 'rca-full-refund-3',
                'customer_refund_id' => 'refund-full-refund-1',
                'customer_payment_id' => 'payment-full-refund-1',
                'note_id' => 'note-full-refund-1',
                'work_item_id' => 'wi-full-refund-external-1',
                'component_type' => 'service_external_purchase_part',
                'component_ref_id' => 'ext-full-refund-1',
                'refunded_amount_rupiah' => 21000,
                'refund_priority' => 3,
            ],
            [
                'id' => 'rca-full-refund-4',
                'customer_refund_id' => 'refund-full-refund-1',
                'customer_payment_id' => 'payment-full-refund-1',
                'note_id' => 'note-full-refund-1',
                'work_item_id' => 'wi-full-refund-external-1',
                'component_type' => 'service_fee',
                'component_ref_id' => 'wi-full-refund-external-1',
                'refunded_amount_rupiah' => 40000,
                'refund_priority' => 4,
            ],
        ]);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'movement-full-refund-stock-out-1',
                'product_id' => 'product-full-refund-1',
                'movement_type' => 'stock_out',
                'source_type' => 'work_item_store_stock_line',
                'source_id' => 'ssl-full-refund-1',
                'tanggal_mutasi' => '2026-04-02',
                'qty_delta' => -1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => -10000,
            ],
            [
                'id' => 'movement-full-refund-stock-return-1',
                'product_id' => 'product-full-refund-1',
                'movement_type' => 'stock_in',
                'source_type' => 'work_item_store_stock_line_reversal',
                'source_id' => 'ssl-full-refund-1',
                'tanggal_mutasi' => '2026-04-02',
                'qty_delta' => 1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 10000,
            ],
        ]);

        $result = app(GetOperationalProfitSummaryHandler::class)
            ->handle('2026-04-02', '2026-04-02');

        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);
        $this->assertIsArray($data['row'] ?? null);

        $this->assertSame([
            'from_date' => '2026-04-02',
            'to_date' => '2026-04-02',
            'cash_in_rupiah' => 203000,
            'refunded_rupiah' => 203000,
            'external_purchase_cost_rupiah' => 0,
            'store_stock_cogs_rupiah' => 0,
            'product_purchase_cost_rupiah' => 0,
            'operational_expense_rupiah' => 0,
            'payroll_disbursement_rupiah' => 0,
            'employee_debt_cash_out_rupiah' => 0,
            'cash_operational_profit_rupiah' => 0,
        ], $data['row']);
    }


    public function test_get_operational_profit_summary_handler_offsets_store_stock_cogs_when_refunded_stock_returns_to_inventory(): void
    {
        $this->seedProduct('product-refund-1', 'KB-RFD-001', 'Ban Refund', 'Federal', 100, 100000);

        DB::table('notes')->insert([
            'id' => 'note-refund-1',
            'customer_name' => 'Budi Refund',
            'transaction_date' => '2026-04-01',
            'total_rupiah' => 100000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'wi-refund-1',
            'note_id' => 'note-refund-1',
            'line_no' => 1,
            'transaction_type' => 'store_stock_sale_only',
            'status' => 'open',
            'subtotal_rupiah' => 100000,
        ]);

        DB::table('work_item_store_stock_lines')->insert([
            'id' => 'ssl-refund-1',
            'work_item_id' => 'wi-refund-1',
            'product_id' => 'product-refund-1',
            'qty' => 1,
            'line_total_rupiah' => 100000,
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'payment-refund-1',
            'amount_rupiah' => 100000,
            'paid_at' => '2026-04-01',
        ]);

        DB::table('customer_refunds')->insert([
            'id' => 'refund-refund-1',
            'customer_payment_id' => 'payment-refund-1',
            'note_id' => 'note-refund-1',
            'amount_rupiah' => 100000,
            'refunded_at' => '2026-04-01 10:00:00',
            'reason' => 'Refund penuh barang kembali',
        ]);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'movement-sale-refund-1',
                'product_id' => 'product-refund-1',
                'movement_type' => 'stock_out',
                'source_type' => 'work_item_store_stock_line',
                'source_id' => 'ssl-refund-1',
                'tanggal_mutasi' => '2026-04-01',
                'qty_delta' => -1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => -10000,
            ],
            [
                'id' => 'movement-return-refund-1',
                'product_id' => 'product-refund-1',
                'movement_type' => 'stock_in',
                'source_type' => 'work_item_store_stock_line_reversal',
                'source_id' => 'ssl-refund-1',
                'tanggal_mutasi' => '2026-04-01',
                'qty_delta' => 1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 10000,
            ],
        ]);

        $result = app(GetOperationalProfitSummaryHandler::class)
            ->handle('2026-04-01', '2026-04-01');

        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);
        $this->assertIsArray($data['row'] ?? null);

        $this->assertSame([
            'from_date' => '2026-04-01',
            'to_date' => '2026-04-01',
            'cash_in_rupiah' => 100000,
            'refunded_rupiah' => 100000,
            'external_purchase_cost_rupiah' => 0,
            'store_stock_cogs_rupiah' => 0,
            'product_purchase_cost_rupiah' => 0,
            'operational_expense_rupiah' => 0,
            'payroll_disbursement_rupiah' => 0,
            'employee_debt_cash_out_rupiah' => 0,
            'cash_operational_profit_rupiah' => 0,
        ], $data['row']);
    }


    public function test_get_operational_profit_summary_handler_allows_negative_store_stock_cogs_for_cross_period_refund(): void
    {
        $this->seedProduct('product-cross-refund-1', 'KB-XR-001', 'Ban Cross Refund', 'Federal', 100, 100000);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'movement-cross-sale-1',
                'product_id' => 'product-cross-refund-1',
                'movement_type' => 'stock_out',
                'source_type' => 'work_item_store_stock_line',
                'source_id' => 'ssl-cross-refund-1',
                'tanggal_mutasi' => '2026-04-30',
                'qty_delta' => -1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => -10000,
            ],
            [
                'id' => 'movement-cross-return-1',
                'product_id' => 'product-cross-refund-1',
                'movement_type' => 'stock_in',
                'source_type' => 'work_item_store_stock_line_reversal',
                'source_id' => 'ssl-cross-refund-1',
                'tanggal_mutasi' => '2026-05-01',
                'qty_delta' => 1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 10000,
            ],
        ]);

        $result = app(GetOperationalProfitSummaryHandler::class)
            ->handle('2026-05-01', '2026-05-01');

        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);
        $this->assertIsArray($data['row'] ?? null);

        $this->assertSame([
            'from_date' => '2026-05-01',
            'to_date' => '2026-05-01',
            'cash_in_rupiah' => 0,
            'refunded_rupiah' => 0,
            'external_purchase_cost_rupiah' => 0,
            'store_stock_cogs_rupiah' => -10000,
            'product_purchase_cost_rupiah' => -10000,
            'operational_expense_rupiah' => 0,
            'payroll_disbursement_rupiah' => 0,
            'employee_debt_cash_out_rupiah' => 0,
            'cash_operational_profit_rupiah' => 10000,
        ], $data['row']);
    }

    public function test_get_operational_profit_summary_handler_excludes_reversed_payroll_from_profit_metrics(): void
    {
        $this->seedEmployee('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'Montir Reversal');

        DB::table('payroll_disbursements')->insert([
            [
                'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'employee_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'amount' => 40000,
                'disbursement_date' => '2026-03-16 12:00:00',
                'mode' => 'weekly',
                'notes' => 'Payroll direversal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'employee_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'amount' => 10000,
                'disbursement_date' => '2026-03-16 13:00:00',
                'mode' => 'weekly',
                'notes' => 'Payroll aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('payroll_disbursement_reversals')->insert([
            'id' => 'dddddddd-dddd-dddd-dddd-dddddddddddd',
            'payroll_disbursement_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'reason' => 'Koreksi payout payroll',
            'performed_by_actor_id' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(GetOperationalProfitSummaryHandler::class)
            ->handle('2026-03-15', '2026-03-16');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());

        $data = $result->data();

        $this->assertSame([
            'from_date' => '2026-03-15',
            'to_date' => '2026-03-16',
            'cash_in_rupiah' => 0,
            'refunded_rupiah' => 0,
            'external_purchase_cost_rupiah' => 0,
            'store_stock_cogs_rupiah' => 0,
            'product_purchase_cost_rupiah' => 0,
            'operational_expense_rupiah' => 0,
            'payroll_disbursement_rupiah' => 10000,
            'employee_debt_cash_out_rupiah' => 0,
            'cash_operational_profit_rupiah' => -10000,
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
