<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Application\ProductCatalog\Services\EditProductPageData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditProductPageController extends Controller
{
    public function __construct(
        private readonly EditProductPageData $pageData,
    ) {
    }

    public function __invoke(string $productId): View|RedirectResponse
    {
        $data = $this->pageData->getById($productId);

        if ($data === null) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', 'Product tidak ditemukan.');
        }

        return view('admin.products.edit', $data);
    }
}
