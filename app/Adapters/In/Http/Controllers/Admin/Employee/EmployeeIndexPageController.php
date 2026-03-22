<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class EmployeeIndexPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.employees.index');
    }
}
