<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\Services\ProcurementProductLookupData;
use App\Application\ProductCatalog\DTO\ProductLookupRow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ProductLookupController extends Controller
{
    public function __invoke(
        Request $request,
        ProcurementProductLookupData $lookupData,
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
            $this->toRow(...),
            $lookupData->search($query),
        );

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $rows,
            ],
        ]);
    }

    /**
     * @return array{id:string,label:string,kode_barang:?string,nama_barang:string,merek:string,ukuran:?int}
     */
    private function toRow(ProductLookupRow $product): array
    {
        return [
            'id' => $product->id,
            'label' => $product->label(),
            'kode_barang' => $product->kodeBarang,
            'nama_barang' => $product->namaBarang,
            'merek' => $product->merek,
            'ukuran' => $product->ukuran,
        ];
    }
}
