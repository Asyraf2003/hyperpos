<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeDebtListPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class EmployeeDebtIndexPageController extends Controller
{
    public function __invoke(DatabaseEmployeeDebtListPageQuery $query): View
    {
        return view('admin.employee_debts.index', [
            'rows' => $query->latest(),
        ]);
    }
}
