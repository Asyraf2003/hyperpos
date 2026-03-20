<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Procurement\SupplierPaymentProofAttachment\SupplierPaymentProofAttachment;

interface SupplierPaymentProofAttachmentReaderPort
{
    /**
     * @return list<SupplierPaymentProofAttachment>
     */
    public function listBySupplierPaymentId(string $supplierPaymentId): array;
}
