<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Requests\Procurement\ReceiveSupplierInvoiceRequest;
use App\Application\Procurement\UseCases\ReceiveSupplierInvoiceHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class ReceiveSupplierInvoiceController extends Controller
{
    public function __invoke(
        string $supplierInvoiceId,
        ReceiveSupplierInvoiceRequest $request,
        ReceiveSupplierInvoiceHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            $supplierInvoiceId,
            (string) $data['tanggal_terima'],
            $data['lines'],
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'supplier_receipt' => $result->message() ?? 'Penerimaan barang gagal dicatat.',
                ])
                ->withInput();
        }

        return back()->with('success', $result->message() ?? 'Penerimaan barang berhasil dicatat.');
    }
}
