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

        return view('admin.dashboard.index', [
            'dashboard' => $dashboard,
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
