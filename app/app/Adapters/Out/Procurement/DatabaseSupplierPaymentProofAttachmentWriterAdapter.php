<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Core\Procurement\SupplierPaymentProofAttachment\SupplierPaymentProofAttachment;
use App\Ports\Out\Procurement\SupplierPaymentProofAttachmentWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierPaymentProofAttachmentWriterAdapter implements SupplierPaymentProofAttachmentWriterPort
{
    public function createMany(array $attachments): void
    {
        if ($attachments === []) {
            return;
        }

        DB::table('supplier_payment_proof_attachments')->insert(
            array_map(
                fn (SupplierPaymentProofAttachment $attachment): array => $this->toRecord($attachment),
                $attachments,
            )
        );
    }

    /**
     * @return array<string, string|int>
     */
    private function toRecord(SupplierPaymentProofAttachment $attachment): array
    {
        return [
            'id' => $attachment->id(),
            'supplier_payment_id' => $attachment->supplierPaymentId(),
            'storage_path' => $attachment->storagePath(),
            'original_filename' => $attachment->originalFilename(),
            'mime_type' => $attachment->mimeType(),
            'file_size_bytes' => $attachment->fileSizeBytes(),
            'uploaded_at' => $attachment->uploadedAt()->format('Y-m-d H:i:s'),
            'uploaded_by_actor_id' => $attachment->uploadedByActorId(),
        ];
    }
}
