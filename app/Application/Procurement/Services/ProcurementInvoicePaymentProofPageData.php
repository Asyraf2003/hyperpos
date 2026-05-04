<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\SupplierPayment\SupplierPayment;
use App\Core\Procurement\SupplierPaymentProofAttachment\SupplierPaymentProofAttachment;
use App\Ports\Out\Procurement\SupplierPaymentProofAttachmentReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;

final class ProcurementInvoicePaymentProofPageData
{
    public function __construct(
        private readonly SupplierPaymentReaderPort $payments,
        private readonly SupplierPaymentProofAttachmentReaderPort $attachments,
    ) {
    }

    /**
     * @return array{
     *     paymentRows: list<SupplierPayment>,
     *     attachmentMap: array<string, list<SupplierPaymentProofAttachment>>
     * }
     */
    public function load(string $supplierInvoiceId): array
    {
        $paymentRows = $this->payments->listBySupplierInvoiceId($supplierInvoiceId);
        $attachmentMap = [];

        foreach ($paymentRows as $payment) {
            $attachmentMap[$payment->id()] = $this->attachments->listBySupplierPaymentId($payment->id());
        }

        return [
            'paymentRows' => $paymentRows,
            'attachmentMap' => $attachmentMap,
        ];
    }
}
