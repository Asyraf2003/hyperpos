<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Procurement\SupplierPaymentProofAttachment\SupplierPaymentProofAttachment;

interface SupplierPaymentProofAttachmentReaderPort
{
    public function getById(string $attachmentId): ?SupplierPaymentProofAttachment;

    /**
     * @return list<SupplierPaymentProofAttachment>
     */
    public function listBySupplierPaymentId(string $supplierPaymentId): array;
}
