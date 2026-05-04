<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\Services\SupplierLookupData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class SupplierLookupController extends Controller
{
    public function __invoke(
        Request $request,
        SupplierLookupData $lookupData,
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
            static fn ($supplier): array => [
                'id' => $supplier->id(),
                'label' => $supplier->namaPtPengirim(),
                'nama_pt_pengirim' => $supplier->namaPtPengirim(),
            ],
            $lookupData->search($query),
        );

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $rows,
            ],
        ]);
    }
}
