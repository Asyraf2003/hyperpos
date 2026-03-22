<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeListPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreateEmployeeDebtPageController extends Controller
{
    public function __invoke(DatabaseEmployeeListPageQuery $employeeQuery): View
    {
        return view('admin.employee_debts.create', [
            'employees' => $employeeQuery->all(),
        ]);
    }
}
