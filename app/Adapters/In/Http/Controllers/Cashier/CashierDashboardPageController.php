<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CashierDashboardPageController extends Controller
{
    public function __invoke(): View
    {
        return view('cashier.dashboard.index');
    }
}
