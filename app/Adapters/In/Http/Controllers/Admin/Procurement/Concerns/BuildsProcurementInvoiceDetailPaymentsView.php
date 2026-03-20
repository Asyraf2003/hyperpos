<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns;

use App\Core\Procurement\SupplierPayment\SupplierPayment;

trait BuildsProcurementInvoiceDetailPaymentsView
{
    /**
     * @param array<int, SupplierPayment> $payments
     * @return array<int, array<string, string|bool|null>>
     */
    private function buildPaymentsView(array $payments): array
    {
        $paymentViews = [];

        foreach ($payments as $payment) {
            $paymentViews[] = [
                'id' => $payment->id(),
                'amount_label' => $this->formatRupiah($payment->amountRupiah()->amount()),
                'paid_at' => $payment->paidAt()->format('Y-m-d'),
                'proof_status_label' => $payment->proofStatus() === SupplierPayment::PROOF_STATUS_UPLOADED
                    ? 'Uploaded'
                    : 'Pending',
                'proof_storage_path' => $payment->proofStoragePath(),
                'can_attach_proof' => $payment->proofStatus() === SupplierPayment::PROOF_STATUS_PENDING,
            ];
        }

        return $paymentViews;
    }
}
