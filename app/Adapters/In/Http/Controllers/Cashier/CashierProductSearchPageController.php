<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CashierProductSearchPageController extends Controller
{
    public function __invoke(): View
    {
        return view('cashier.dashboard.product-search', [
            'productLookupEndpoint' => route('cashier.notes.products.lookup'),
        ]);
    }
}
