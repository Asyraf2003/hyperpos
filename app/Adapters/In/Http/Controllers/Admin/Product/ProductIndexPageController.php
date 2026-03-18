<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class ProductIndexPageController extends Controller
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    public function __invoke(): View
    {
        return view('admin.products.index', [
            'products' => $this->products->findAll(),
        ]);
    }
}
