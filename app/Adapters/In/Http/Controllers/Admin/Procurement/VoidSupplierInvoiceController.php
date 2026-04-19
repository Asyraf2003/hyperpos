<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Requests\Procurement\VoidSupplierInvoiceRequest;
use App\Application\Procurement\UseCases\VoidSupplierInvoiceHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class VoidSupplierInvoiceController extends Controller
{
    public function __invoke(
        VoidSupplierInvoiceRequest $request,
        VoidSupplierInvoiceHandler $useCase,
        string $supplierInvoiceId,
    ): RedirectResponse {
        $data = $request->validated();

        $user = $request->user();
        $actorId = $user?->getAuthIdentifier();

        $result = $useCase->handle(
            $supplierInvoiceId,
            (string) $data['void_reason'],
            $actorId !== null ? (string) $actorId : null,
        );

        if ($result->isFailure()) {
            $errors = $result->errors()['supplier_invoice'] ?? [];

            if ($errors === ['SUPPLIER_INVOICE_NOT_FOUND']) {
                return redirect()
                    ->route('admin.procurement.supplier-invoices.index')
                    ->with('error', $result->message() ?? 'Nota supplier tidak ditemukan.');
            }

            return back()
                ->withErrors([
                    'supplier_invoice' => $result->message() ?? 'Nota supplier gagal dibatalkan.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => $supplierInvoiceId])
            ->with('success', $result->message() ?? 'Nota supplier berhasil dibatalkan.');
    }
}
