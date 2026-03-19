<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Requests\Procurement\CreateSupplierInvoiceRequest;
use App\Application\Procurement\UseCases\CreateSupplierInvoiceFlowHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class StoreSupplierInvoiceController extends Controller
{
    public function __invoke(
        CreateSupplierInvoiceRequest $request,
        CreateSupplierInvoiceFlowHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        $autoReceive = array_key_exists('auto_receive', $data)
            ? (bool) $data['auto_receive']
            : true;

        $tanggalTerima = array_key_exists('tanggal_terima', $data) && $data['tanggal_terima'] !== null
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
            return back()
                ->withErrors([
                    'supplier_invoice' => $result->message() ?? 'Nota supplier gagal dibuat.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.procurement.supplier-invoices.index')
            ->with('success', $result->message() ?? 'Nota supplier berhasil dibuat.');
    }
}
