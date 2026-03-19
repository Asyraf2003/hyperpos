<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreateSupplierInvoicePageController extends Controller
{
    public function __invoke(ProductReaderPort $products): View
    {
        return view('admin.procurement.supplier_invoices.create', [
            'products' => $products->findAll(),
        ]);
    }
}
