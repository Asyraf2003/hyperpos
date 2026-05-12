<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Procurement;

use App\Adapters\In\Http\Controllers\Api\V1\Procurement\Support\MobileSupplierInvoiceTableQueryFactory;
use App\Adapters\In\Http\Controllers\Api\V1\Support\MobileApiAdminAccess;
use App\Application\MobileApi\Auth\DTO\MobileApiActor;
use App\Application\Procurement\UseCases\GetProcurementInvoiceTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ListMobileApiSupplierInvoicesController extends Controller
{
    public function __construct(
        private readonly GetProcurementInvoiceTableHandler $invoices,
        private readonly MobileApiAdminAccess $access,
        private readonly MobileSupplierInvoiceTableQueryFactory $queries,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $actor = $this->access->actorOrError($request, 'Akses nota supplier mobile hanya untuk admin.');

        if (! $actor instanceof MobileApiActor) {
            return $actor;
        }

        $result = $this->invoices->handle($this->queries->fromRequest($request));

        /** @var array{rows:list<array<string, mixed>>, meta:array<string, mixed>} $payload */
        $payload = $result->data();

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $payload['rows'],
            ],
            'meta' => $payload['meta'],
            'errors' => null,
        ]);
    }
}
