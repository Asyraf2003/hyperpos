<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ProductLookupController extends Controller
{
    public function __invoke(
        Request $request,
        ProductReaderPort $products,
        ProductInventoryReaderPort $inventories,
    ): JsonResponse {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'rows' => [],
                ],
            ]);
        }

        $rows = [];

        foreach ($products->search($query) as $product) {
            $inventory = $inventories->getByProductId($product->id());
            $availableStock = $inventory?->qtyOnHand() ?? 0;

            if ($availableStock <= 0) {
                continue;
            }

            $floorPrice = $product->hargaJual()->amount();

            $parts = [
                $product->namaBarang(),
                $product->merek(),
            ];

            if ($product->ukuran() !== null) {
                $parts[] = (string) $product->ukuran();
            }

            $label = implode(' — ', $parts);

            if ($product->kodeBarang() !== null) {
                $label .= ' (' . $product->kodeBarang() . ')';
            }

            $rows[] = [
                'id' => $product->id(),
                'label' => $label,
                'available_stock' => $availableStock,
                'default_unit_price_rupiah' => $floorPrice,
                'minimum_unit_price_rupiah' => $floorPrice,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $rows,
            ],
        ]);
    }
}
