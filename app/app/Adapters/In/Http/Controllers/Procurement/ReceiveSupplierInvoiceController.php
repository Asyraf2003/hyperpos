<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Procurement;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Procurement\ReceiveSupplierInvoiceRequest;
use App\Application\Procurement\UseCases\ReceiveSupplierInvoiceHandler;
use Illuminate\Http\JsonResponse;

final class ReceiveSupplierInvoiceController
{
    public function __invoke(
        string $supplierInvoiceId,
        ReceiveSupplierInvoiceRequest $request,
        ReceiveSupplierInvoiceHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            $supplierInvoiceId,
            (string) $data['tanggal_terima'],
            $data['lines'],
        );

        if ($result->isFailure()) {
            return $presenter->failure($result);
        }

        return $presenter->success($result);
    }
}
