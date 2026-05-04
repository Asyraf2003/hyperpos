<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Application\EmployeeFinance\Services\CreateEmployeeDebtPageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CreateEmployeeDebtPageController extends Controller
{
    public function __invoke(Request $request, CreateEmployeeDebtPageDataBuilder $pageData): View
    {
        return view('admin.employee_debts.create', $pageData->build($request->query('employee_id')));
    }
}
