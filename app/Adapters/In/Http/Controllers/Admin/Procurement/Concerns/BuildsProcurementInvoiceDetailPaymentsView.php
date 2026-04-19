<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns;

use App\Core\Procurement\SupplierPayment\SupplierPayment;
use App\Core\Procurement\SupplierPaymentProofAttachment\SupplierPaymentProofAttachment;

trait BuildsProcurementInvoiceDetailPaymentsView
{
    /**
     * @param array<int, SupplierPayment> $payments
     * @param array<string, list<SupplierPaymentProofAttachment>> $attachmentMap
     * @return array<int, array<string, mixed>>
     */
    private function buildPaymentsView(array $payments, array $attachmentMap): array
    {
        $paymentViews = [];

        foreach ($payments as $payment) {
            $attachments = $attachmentMap[$payment->id()] ?? [];

            $attachmentViews = array_map(
                fn (SupplierPaymentProofAttachment $attachment): array => [
                    'id' => $attachment->id(),
                    'storage_path' => $attachment->storagePath(),
                    'original_filename' => $attachment->originalFilename(),
                    'mime_type' => $attachment->mimeType(),
                    'file_size_bytes' => $attachment->fileSizeBytes(),
                    'uploaded_at' => $attachment->uploadedAt()->format('Y-m-d H:i:s'),
                    'uploaded_by_actor_id' => $attachment->uploadedByActorId(),
                ],
                $attachments,
            );

            $paymentViews[] = [
                'id' => $payment->id(),
                'amount_label' => $this->formatRupiah($payment->amountRupiah()->amount()),
                'paid_at' => $payment->paidAt()->format('Y-m-d'),
                'proof_status_label' => $payment->proofStatus() === SupplierPayment::PROOF_STATUS_UPLOADED
                    ? 'Sudah Ada Bukti'
                    : 'Belum Ada Bukti',
                'proof_storage_path' => $payment->proofStoragePath(),
                'can_attach_proof' => true,
                'attachment_count' => count($attachmentViews),
                'attachments' => $attachmentViews,
            ];
        }

        return $paymentViews;
    }
}
