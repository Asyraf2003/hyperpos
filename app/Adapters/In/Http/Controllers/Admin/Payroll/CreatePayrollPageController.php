<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Payroll;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeListPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreatePayrollPageController extends Controller
{
    public function __invoke(DatabaseEmployeeListPageQuery $employeeQuery): View
    {
        return view('admin.payrolls.create', [
            'employees' => $employeeQuery->all(),
        ]);
    }
}
