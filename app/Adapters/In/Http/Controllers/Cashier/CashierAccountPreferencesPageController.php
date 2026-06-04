<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CashierAccountPreferencesPageController extends Controller
{
    public function __invoke(): View
    {
        return view('cashier.dashboard.account-preferences');
    }
}
