<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ProductIndexPageController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $products = $query === ''
            ? $this->products->findPaginated(self::PER_PAGE)
            : $this->products->searchPaginated($query, self::PER_PAGE);

        if ($query !== '') {
            $products->appends(['q' => $query]);
        }

        return view('admin.products.index', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}
