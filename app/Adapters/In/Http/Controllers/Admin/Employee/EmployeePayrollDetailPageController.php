<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Application\EmployeeFinance\Services\EmployeePayrollDetailPageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EmployeePayrollDetailPageController extends Controller
{
    public function __invoke(
        string $employeeId,
        EmployeePayrollDetailPageDataBuilder $pageData,
    ): View|RedirectResponse {
        $data = $pageData->build($employeeId);

        if ($data === null) {
            return redirect()
                ->route('admin.employees.index')
                ->with('error', 'Data karyawan tidak ditemukan.');
        }

        return view('admin.employees.payrolls', $data);
    }
}
