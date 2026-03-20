<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns\BuildsProcurementInvoiceDetailViewData;
use App\Application\Procurement\UseCases\GetProcurementInvoiceDetailHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class ProcurementInvoiceDetailPageController extends Controller
{
    use BuildsProcurementInvoiceDetailViewData;

    public function __invoke(
        string $supplierInvoiceId,
        GetProcurementInvoiceDetailHandler $useCase,
    ): View|RedirectResponse {
        $result = $useCase->handle($supplierInvoiceId);

        if ($result->isFailure()) {
            return redirect()
                ->route('admin.procurement.supplier-invoices.index')
                ->with('error', $result->message() ?? 'Nota supplier tidak ditemukan.');
        }

        /** @var array<string, mixed> $detail */
        $detail = $result->data();

        return view(
            'admin.procurement.supplier_invoices.show',
            $this->buildViewData($detail),
        );
    }
}
