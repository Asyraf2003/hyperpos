<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Application\EmployeeFinance\Services\EditEmployeePageData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditEmployeePageController extends Controller
{
    public function __construct(
        private readonly EditEmployeePageData $pageData,
    ) {
    }

    public function __invoke(string $employeeId): View|RedirectResponse
    {
        $employee = $this->pageData->findById($employeeId);

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
