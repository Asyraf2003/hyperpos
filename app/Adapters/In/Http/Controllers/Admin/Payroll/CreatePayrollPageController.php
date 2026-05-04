<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Payroll;

use App\Application\EmployeeFinance\Services\CreatePayrollPageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreatePayrollPageController extends Controller
{
    public function __invoke(CreatePayrollPageDataBuilder $pageData): View
    {
        return view('admin.payrolls.create', $pageData->build());
    }
}
