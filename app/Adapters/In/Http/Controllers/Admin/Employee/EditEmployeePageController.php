<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditEmployeePageController extends Controller
{
    public function __invoke(string $employeeId, EmployeeReaderPort $employeeReader): View|RedirectResponse
    {
        $employee = $employeeReader->findById($employeeId);

        if ($employee === null) {
            return redirect()
                ->route('admin.employees.index')
                ->with('error', 'Data karyawan tidak ditemukan.');
        }

        return view('admin.employees.edit', [
            'employee' => $employee,
        ]);
    }
}
