<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ProductLookupController extends Controller
{
    public function __invoke(
        Request $request,
        ProductReaderPort $products,
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

        $rows = array_map(
            static function ($product): array {
                $kode = $product->kodeBarang();
                $ukuran = $product->ukuran();

                $parts = [
                    $product->namaBarang(),
                    $product->merek(),
                ];

                if ($ukuran !== null) {
                    $parts[] = (string) $ukuran;
                }

                $label = implode(' — ', $parts);

                if ($kode !== null) {
                    $label .= ' (' . $kode . ')';
                }

                return [
                    'id' => $product->id(),
                    'label' => $label,
                    'kode_barang' => $kode,
                    'nama_barang' => $product->namaBarang(),
                    'merek' => $product->merek(),
                    'ukuran' => $ukuran,
                ];
            },
            $products->search($query),
        );

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $rows,
            ],
        ]);
    }
}
