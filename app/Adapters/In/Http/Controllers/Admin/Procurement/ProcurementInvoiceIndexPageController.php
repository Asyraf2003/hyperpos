<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class ProcurementInvoiceIndexPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.procurement.supplier_invoices.index');
    }
}
