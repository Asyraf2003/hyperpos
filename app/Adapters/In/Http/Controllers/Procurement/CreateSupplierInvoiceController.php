<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Procurement;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Procurement\CreateSupplierInvoiceRequest;
use App\Application\Procurement\UseCases\CreateSupplierInvoiceHandler;
use Illuminate\Http\JsonResponse;

final class CreateSupplierInvoiceController
{
    public function __invoke(
        CreateSupplierInvoiceRequest $request,
        CreateSupplierInvoiceHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            (string) $data['nama_pt_pengirim'],
            (string) $data['tanggal_pengiriman'],
            $data['lines'],
        );

        if ($result->isFailure()) {
            return $presenter->failure($result);
        }

        return $presenter->success($result);
    }
}
