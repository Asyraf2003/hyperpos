<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Supplier;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class SupplierIndexPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.suppliers.index');
    }
}
