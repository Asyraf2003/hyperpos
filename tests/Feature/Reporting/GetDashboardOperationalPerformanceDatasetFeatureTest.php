<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetDashboardOperationalPerformanceDatasetHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetDashboardOperationalPerformanceDatasetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_operational_performance_offsets_store_stock_cogs_when_refunded_stock_returns_to_inventory(): void
    {
        DB::table('products')->insert([
            'id' => 'product-dashboard-refund-1',
            'kode_barang' => 'DB-RFD-001',
            'nama_barang' => 'Produk Dashboard Refund',
            'nama_barang_normalized' => 'produk dashboard refund',
            'merek' => 'Federal',
            'merek_normalized' => 'federal',
            'ukuran' => 100,
            'harga_jual' => 100000,
            'deleted_at' => null,
            'deleted_by_actor_id' => null,
            'delete_reason' => null,
        ]);

        DB::table('notes')->insert([
            'id' => 'note-dashboard-refund-1',
            'customer_name' => 'Budi Dashboard Refund',
            'transaction_date' => '2026-04-01',
            'total_rupiah' => 100000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'wi-dashboard-refund-1',
            'note_id' => 'note-dashboard-refund-1',
            'line_no' => 1,
            'transaction_type' => 'store_stock_sale_only',
            'status' => 'open',
            'subtotal_rupiah' => 100000,
        ]);

        DB::table('work_item_store_stock_lines')->insert([
            'id' => 'ssl-dashboard-refund-1',
            'work_item_id' => 'wi-dashboard-refund-1',
            'product_id' => 'product-dashboard-refund-1',
            'qty' => 1,
            'line_total_rupiah' => 100000,
        ]);

        DB::table('customer_payments')->insert([
            'id' => 'payment-dashboard-refund-1',
            'amount_rupiah' => 100000,
            'paid_at' => '2026-04-01',
        ]);

        DB::table('customer_refunds')->insert([
            'id' => 'refund-dashboard-refund-1',
            'customer_payment_id' => 'payment-dashboard-refund-1',
            'note_id' => 'note-dashboard-refund-1',
            'amount_rupiah' => 100000,
            'refunded_at' => '2026-04-01 10:00:00',
            'reason' => 'Refund penuh barang kembali',
        ]);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'movement-dashboard-sale-1',
                'product_id' => 'product-dashboard-refund-1',
                'movement_type' => 'stock_out',
                'source_type' => 'work_item_store_stock_line',
                'source_id' => 'ssl-dashboard-refund-1',
                'tanggal_mutasi' => '2026-04-01',
                'qty_delta' => -1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => -10000,
            ],
            [
                'id' => 'movement-dashboard-return-1',
                'product_id' => 'product-dashboard-refund-1',
                'movement_type' => 'stock_in',
                'source_type' => 'work_item_store_stock_line_reversal',
                'source_id' => 'ssl-dashboard-refund-1',
                'tanggal_mutasi' => '2026-04-01',
                'qty_delta' => 1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 10000,
            ],
        ]);

        $dataset = app(GetDashboardOperationalPerformanceDatasetHandler::class)
            ->handle('2026-04-01', '2026-04-01');

        $this->assertSame([
            [
                'period_key' => '2026-04-01',
                'period_label' => '2026-04-01',
                'operational_profit_rupiah' => 0,
                'operational_expense_rupiah' => 0,
                'refund_rupiah' => 100000,
                'potential_change_rupiah' => 0,
            ],
        ], $dataset['period_rows']);

        $this->assertSame([
            'total_operational_profit_rupiah' => 0,
            'total_operational_expense_rupiah' => 0,
            'total_refund_rupiah' => 100000,
            'total_potential_change_rupiah' => 0,
        ], $dataset['summary']);
    }

    public function test_dashboard_operational_performance_allows_negative_store_stock_cogs_for_cross_period_refund(): void
    {
        DB::table('products')->insert([
            'id' => 'product-dashboard-cross-refund-1',
            'kode_barang' => 'DB-XR-001',
            'nama_barang' => 'Produk Dashboard Cross Refund',
            'nama_barang_normalized' => 'produk dashboard cross refund',
            'merek' => 'Federal',
            'merek_normalized' => 'federal',
            'ukuran' => 100,
            'harga_jual' => 100000,
            'deleted_at' => null,
            'deleted_by_actor_id' => null,
            'delete_reason' => null,
        ]);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'movement-dashboard-cross-sale-1',
                'product_id' => 'product-dashboard-cross-refund-1',
                'movement_type' => 'stock_out',
                'source_type' => 'work_item_store_stock_line',
                'source_id' => 'ssl-dashboard-cross-refund-1',
                'tanggal_mutasi' => '2026-04-30',
                'qty_delta' => -1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => -10000,
            ],
            [
                'id' => 'movement-dashboard-cross-return-1',
                'product_id' => 'product-dashboard-cross-refund-1',
                'movement_type' => 'stock_in',
                'source_type' => 'work_item_store_stock_line_reversal',
                'source_id' => 'ssl-dashboard-cross-refund-1',
                'tanggal_mutasi' => '2026-05-01',
                'qty_delta' => 1,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 10000,
            ],
        ]);

        $dataset = app(GetDashboardOperationalPerformanceDatasetHandler::class)
            ->handle('2026-05-01', '2026-05-01');

        $this->assertSame([
            [
                'period_key' => '2026-05-01',
                'period_label' => '2026-05-01',
                'operational_profit_rupiah' => 10000,
                'operational_expense_rupiah' => 0,
                'refund_rupiah' => 0,
                'potential_change_rupiah' => 0,
            ],
        ], $dataset['period_rows']);

        $this->assertSame([
            'total_operational_profit_rupiah' => 10000,
            'total_operational_expense_rupiah' => 0,
            'total_refund_rupiah' => 0,
            'total_potential_change_rupiah' => 0,
        ], $dataset['summary']);
    }

    public function test_dashboard_operational_performance_includes_potential_change_without_affecting_profit(): void
    {
        DB::table('customer_payments')->insert([
            [
                'id' => 'payment-dashboard-change-1',
                'amount_rupiah' => 100000,
                'payment_method' => 'cash',
                'paid_at' => '2026-04-03',
            ],
            [
                'id' => 'payment-dashboard-change-2',
                'amount_rupiah' => 50000,
                'payment_method' => 'cash',
                'paid_at' => '2026-04-03',
            ],
            [
                'id' => 'payment-dashboard-change-outside',
                'amount_rupiah' => 70000,
                'payment_method' => 'cash',
                'paid_at' => '2026-04-04',
            ],
        ]);

        DB::table('customer_payment_cash_details')->insert([
            [
                'customer_payment_id' => 'payment-dashboard-change-1',
                'amount_paid_rupiah' => 100000,
                'amount_received_rupiah' => 120000,
                'change_rupiah' => 20000,
            ],
            [
                'customer_payment_id' => 'payment-dashboard-change-2',
                'amount_paid_rupiah' => 50000,
                'amount_received_rupiah' => 100000,
                'change_rupiah' => 50000,
            ],
            [
                'customer_payment_id' => 'payment-dashboard-change-outside',
                'amount_paid_rupiah' => 70000,
                'amount_received_rupiah' => 100000,
                'change_rupiah' => 30000,
            ],
        ]);

        $dataset = app(GetDashboardOperationalPerformanceDatasetHandler::class)
            ->handle('2026-04-03', '2026-04-03');

        $this->assertSame(70000, $dataset['period_rows'][0]['potential_change_rupiah']);
        $this->assertSame(150000, $dataset['period_rows'][0]['operational_profit_rupiah']);
        $this->assertSame(70000, $dataset['summary']['total_potential_change_rupiah']);
        $this->assertSame(150000, $dataset['summary']['total_operational_profit_rupiah']);
    }
}
