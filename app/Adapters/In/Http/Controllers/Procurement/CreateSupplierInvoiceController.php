<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Procurement;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Procurement\CreateSupplierInvoiceRequest;
use App\Application\Procurement\UseCases\CreateSupplierInvoiceFlowHandler;
use Illuminate\Http\JsonResponse;

final class CreateSupplierInvoiceController
{
    public function __invoke(
        CreateSupplierInvoiceRequest $request,
        CreateSupplierInvoiceFlowHandler $useCase,
        JsonPresenter $presenter,
    ): JsonResponse {
        $data = $request->validated();

        $autoReceive = array_key_exists('auto_receive', $data)
            ? (bool) $data['auto_receive']
            : true;

        $tanggalTerima = array_key_exists('tanggal_terima', $data)
            ? (string) $data['tanggal_terima']
            : null;

        $result = $useCase->handle(
            (string) $data['nama_pt_pengirim'],
            (string) $data['tanggal_pengiriman'],
            $data['lines'],
            $autoReceive,
            $tanggalTerima,
        );

        if ($result->isFailure()) {
            return $presenter->failure($result);
        }

        return $presenter->success($result);
    }
}
