<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Procurement\ProcurementInvoiceTableQueryRequest;
use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use App\Application\Procurement\UseCases\GetProcurementInvoiceTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ProcurementInvoiceTableDataController extends Controller
{
    public function __invoke(
        ProcurementInvoiceTableQueryRequest $request,
        GetProcurementInvoiceTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $result = $useCase->handle(
            ProcurementInvoiceTableQuery::fromValidated($request->validated())
        );

        return $presenter->success($result);
    }
}
