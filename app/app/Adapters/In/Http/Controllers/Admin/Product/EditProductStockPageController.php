<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditProductStockPageController extends Controller
{
    public function __construct(
        private readonly ProductReaderPort $products,
        private readonly ProductInventoryReaderPort $inventories,
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

        $inventory = $this->inventories->getByProductId($productId);

        return view('admin.products.stock-edit', [
            'product' => $product,
            'currentStock' => $inventory?->qtyOnHand() ?? 0,
        ]);
    }
}
