<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Payroll;

use App\Adapters\Out\EmployeeFinance\DatabasePayrollListPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class PayrollIndexPageController extends Controller
{
    public function __invoke(DatabasePayrollListPageQuery $query): View
    {
        return view('admin.payrolls.index', [
            'rows' => $query->latest(),
        ]);
    }
}
