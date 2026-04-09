<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\UseCases\GetProcurementInvoiceDetailHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditSupplierInvoicePageController extends Controller
{
    public function __construct(
        private readonly GetProcurementInvoiceDetailHandler $details,
    ) {
    }

    public function __invoke(string $supplierInvoiceId): View|RedirectResponse
    {
        $result = $this->details->handle($supplierInvoiceId);

        if ($result->isFailure()) {
            return redirect()
                ->route('admin.procurement.supplier-invoices.index')
                ->with('error', $result->message() ?? 'Nota supplier tidak ditemukan.');
        }

        $payload = $result->data();
        if (! is_array($payload)) {
            return redirect()
                ->route('admin.procurement.supplier-invoices.index')
                ->with('error', 'Detail nota supplier tidak valid.');
        }

        $summary = is_array($payload['summary'] ?? null) ? $payload['summary'] : [];
        $lines = is_array($payload['lines'] ?? null) ? $payload['lines'] : [];

        $policyState = (string) ($summary['policy_state'] ?? 'locked');

        if ($policyState !== 'editable') {
            return redirect()
                ->route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => $supplierInvoiceId])
                ->with('error', 'Nota supplier ini sudah terkunci. Gunakan correction / reversal.');
        }

        return view('admin.procurement.supplier_invoices.edit', [
            'summary' => $summary,
            'lines' => $lines,
        ]);
    }
}
