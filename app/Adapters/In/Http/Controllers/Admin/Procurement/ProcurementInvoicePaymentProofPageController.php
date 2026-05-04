<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns\BuildsProcurementInvoiceDetailPaymentsView;
use App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns\BuildsProcurementInvoiceDetailViewData;
use App\Application\Procurement\Services\ProcurementInvoicePaymentProofPageData;
use App\Application\Procurement\UseCases\GetProcurementInvoiceDetailHandler;
use App\Core\Procurement\SupplierPayment\SupplierPayment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class ProcurementInvoicePaymentProofPageController extends Controller
{
    use BuildsProcurementInvoiceDetailPaymentsView;
    use BuildsProcurementInvoiceDetailViewData;

    public function __invoke(
        string $supplierInvoiceId,
        GetProcurementInvoiceDetailHandler $useCase,
        ProcurementInvoicePaymentProofPageData $paymentProofPageData,
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

        $paymentProofData = $paymentProofPageData->load($supplierInvoiceId);
        $paymentRows = $paymentProofData['paymentRows'];
        $attachmentMap = $paymentProofData['attachmentMap'];

        $viewData['paymentsView'] = $this->buildPaymentsView(
            $paymentRows,
            $attachmentMap,
        );

        $viewData['paymentStatusView'] = $this->buildPaymentStatusView(
            $detail,
            $paymentRows,
        );

        return view('admin.procurement.supplier_invoices.payment_proofs', $viewData);
    }

    /**
     * @param array<string, mixed> $detail
     * @param array<int, SupplierPayment> $payments
     * @return array<string, int|string>
     */
    private function buildPaymentStatusView(array $detail, array $payments): array
    {
        $summary = is_array($detail['summary'] ?? null) ? $detail['summary'] : [];

        $totalPaidRupiah = (int) ($summary['total_paid_rupiah'] ?? 0);
        $outstandingRupiah = (int) ($summary['outstanding_rupiah'] ?? 0);
        $paymentCount = count($payments);

        if ($paymentCount < 1 || $totalPaidRupiah < 1) {
            return [
                'label' => 'Belum Dibayar',
                'badge_class' => 'bg-light-secondary text-dark',
                'payment_count' => $paymentCount,
            ];
        }

        if ($outstandingRupiah > 0) {
            return [
                'label' => 'Sebagian Dibayar',
                'badge_class' => 'bg-warning text-dark',
                'payment_count' => $paymentCount,
            ];
        }

        return [
            'label' => 'Lunas',
            'badge_class' => 'bg-success',
            'payment_count' => $paymentCount,
        ];
    }
}
