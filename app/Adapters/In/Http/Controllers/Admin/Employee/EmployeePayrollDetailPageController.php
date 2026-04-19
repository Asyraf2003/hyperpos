<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeDetailPageQuery;
use App\Adapters\Out\EmployeeFinance\DatabaseEmployeePayrollSummaryByEmployeeQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EmployeePayrollDetailPageController extends Controller
{
    public function __invoke(
        string $employeeId,
        DatabaseEmployeeDetailPageQuery $query,
        DatabaseEmployeePayrollSummaryByEmployeeQuery $payrollSummaryQuery,
    ): View|RedirectResponse {
        $detail = $query->findById($employeeId);

        if ($detail === null) {
            return redirect()
                ->route('admin.employees.index')
                ->with('error', 'Data karyawan tidak ditemukan.');
        }

        $employee = $detail['page']['current_identity'];

        return view('admin.employees.payrolls', [
            'detail' => $detail,
            'employee' => $employee,
            'page' => [
                'heading' => 'Detail Gaji Karyawan',
                'subtitle' => 'Riwayat gaji, status pencairan, dan koreksi payroll khusus karyawan ini.',
            ],
            'payrollSummary' => $payrollSummaryQuery->findByEmployeeId($employeeId),
        ]);
    }
}
