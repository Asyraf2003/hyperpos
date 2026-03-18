<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditProductPageController extends Controller
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    public function __invoke(string $productId): View|RedirectResponse
    {
        $product = $this->products->getById($productId);

        if ($product === null) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', 'Product tidak ditemukan.');
        }

        return view('admin.products.edit', [
            'product' => $product,
        ]);
    }
}
