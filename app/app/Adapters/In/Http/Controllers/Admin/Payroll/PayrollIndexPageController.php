<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Payroll;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class PayrollIndexPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.payrolls.index');
    }
}
