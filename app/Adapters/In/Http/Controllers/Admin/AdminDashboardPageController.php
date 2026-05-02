<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin;

use App\Application\Reporting\UseCases\GetAdminDashboardPagePayloadHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AdminDashboardPageController extends Controller
{
    public function __invoke(Request $request, GetAdminDashboardPagePayloadHandler $useCase): View
    {
        $month = $request->query('month');

        $dashboard = $useCase->handle(is_string($month) ? $month : null);
        $stockStatusSegments = $dashboard['analytics']['charts']['stock_status_donut']['segments'] ?? [];
        $dashboardStockStatusSegments = array_map(static function ($segment): array {
            $segment = is_array($segment) ? $segment : [];
            $key = (string) ($segment['key'] ?? '');
            $colorToken = (string) ($segment['color_token'] ?? '');
            $severityClass = match (true) {
                $key === 'safe' || $colorToken === 'success' => 'is-safe',
                $key === 'low' || $colorToken === 'warning' => 'is-warning',
                $key === 'critical' || $colorToken === 'danger' => 'is-critical',
                default => 'is-unconfigured',
            };
            $badgeClass = match ($severityClass) {
                'is-safe' => 'bg-soft-success',
                'is-warning' => 'bg-soft-warning',
                'is-critical' => 'bg-soft-danger',
                default => 'bg-soft-info',
            };

            return [
                'label' => (string) ($segment['label'] ?? '-'),
                'value' => (int) ($segment['value'] ?? 0),
                'severity_class' => $severityClass,
                'badge_class' => $badgeClass,
            ];
        }, is_array($stockStatusSegments) ? $stockStatusSegments : []);

        return view('admin.dashboard.index', [
            'dashboard' => $dashboard,
            'dashboardStockStatusSegments' => $dashboardStockStatusSegments,
            'dashboardExportQuery' => [
                'period_mode' => 'monthly',
                'reference_date' => (string) ($dashboard['period']['date_to'] ?? now()->toDateString()),
            ],
            'dashboardReportExportShortcuts' => [
                [
                    'label' => 'Laporan Transaksi',
                    'index' => 'admin.reports.transaction_summary.index',
                    'pdf' => 'admin.reports.transaction_summary.export_pdf',
                    'excel' => 'admin.reports.transaction_summary.export_excel',
                ],
                [
                    'label' => 'Buku Kas Transaksi',
                    'index' => 'admin.reports.transaction_cash_ledger.index',
                    'pdf' => 'admin.reports.transaction_cash_ledger.export_pdf',
                    'excel' => 'admin.reports.transaction_cash_ledger.export_excel',
                ],
                [
                    'label' => 'Stok dan Nilai Persediaan',
                    'index' => 'admin.reports.inventory_stock_value.index',
                    'pdf' => 'admin.reports.inventory_stock_value.export_pdf',
                    'excel' => 'admin.reports.inventory_stock_value.export_excel',
                ],
                [
                    'label' => 'Laba Kas Operasional',
                    'index' => 'admin.reports.operational_profit.index',
                    'pdf' => 'admin.reports.operational_profit.export_pdf',
                    'excel' => 'admin.reports.operational_profit.export_excel',
                ],
                [
                    'label' => 'Biaya Operasional',
                    'index' => 'admin.reports.operational_expense.index',
                    'pdf' => 'admin.reports.operational_expense.export_pdf',
                    'excel' => 'admin.reports.operational_expense.export_excel',
                ],
                [
                    'label' => 'Payroll',
                    'index' => 'admin.reports.payroll.index',
                    'pdf' => 'admin.reports.payroll.export_pdf',
                    'excel' => 'admin.reports.payroll.export_excel',
                ],
                [
                    'label' => 'Hutang Karyawan',
                    'index' => 'admin.reports.employee_debt.index',
                    'pdf' => 'admin.reports.employee_debt.export_pdf',
                    'excel' => 'admin.reports.employee_debt.export_excel',
                ],
                [
                    'label' => 'Hutang Pemasok',
                    'index' => 'admin.reports.supplier_payable.index',
                    'pdf' => 'admin.reports.supplier_payable.export_pdf',
                    'excel' => 'admin.reports.supplier_payable.export_excel',
                ],
            ],
        ]);
    }
}
