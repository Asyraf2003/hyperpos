<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeListPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class EmployeeIndexPageController extends Controller
{
    public function __invoke(DatabaseEmployeeListPageQuery $query): View
    {
        return view('admin.employees.index', [
            'employees' => $query->all(),
        ]);
    }
}
