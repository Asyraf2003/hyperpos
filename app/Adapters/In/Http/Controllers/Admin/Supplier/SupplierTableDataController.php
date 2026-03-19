<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Supplier;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Procurement\SupplierTableQueryRequest;
use App\Application\Procurement\DTO\SupplierTableQuery;
use App\Application\Procurement\UseCases\GetSupplierTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class SupplierTableDataController extends Controller
{
    public function __invoke(
        SupplierTableQueryRequest $request,
        GetSupplierTableHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $result = $useCase->handle(SupplierTableQuery::fromValidated($request->validated()));

        return $presenter->success($result);
    }
}
