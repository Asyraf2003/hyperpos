<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class EmployeeDebtIndexPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.employee_debts.index');
    }
}
