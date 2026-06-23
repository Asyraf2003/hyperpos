<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Ports\Out\ServiceProductTemplate\ServiceProductTemplateLookupReaderPort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class PackageLookupController extends Controller
{
    public function __construct(private readonly PackageLookupResponseMapper $rows)
    {
    }

    public function __invoke(Request $request, ServiceProductTemplateLookupReaderPort $packages): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $this->rows->mapMany($packages->searchActivePackages($query)),
            ],
        ]);
    }
}
