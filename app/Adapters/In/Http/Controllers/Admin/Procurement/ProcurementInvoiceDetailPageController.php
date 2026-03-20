<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns\BuildsProcurementInvoiceDetailPaymentsView;
use App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns\BuildsProcurementInvoiceDetailViewData;
use App\Application\Procurement\UseCases\GetProcurementInvoiceDetailHandler;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class ProcurementInvoiceDetailPageController extends Controller
{
    use BuildsProcurementInvoiceDetailPaymentsView;
    use BuildsProcurementInvoiceDetailViewData;

    public function __invoke(
        string $supplierInvoiceId,
        GetProcurementInvoiceDetailHandler $useCase,
        SupplierPaymentReaderPort $payments,
    ): View|RedirectResponse {
        $result = $useCase->handle($supplierInvoiceId);

        if ($result->isFailure()) {
            return redirect()
                ->route('admin.procurement.supplier-invoices.index')
                ->with('error', $result->message() ?? 'Nota supplier tidak ditemukan.');
        }

        /** @var array<string, mixed> $detail */
        $detail = $result->data();
        $viewData = $this->buildViewData($detail);
        $viewData['paymentsView'] = $this->buildPaymentsView(
            $payments->listBySupplierInvoiceId($supplierInvoiceId),
        );

        return view('admin.procurement.supplier_invoices.show', $viewData);
    }
}
