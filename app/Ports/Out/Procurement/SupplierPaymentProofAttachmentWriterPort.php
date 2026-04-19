<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Procurement\SupplierPaymentProofAttachment\SupplierPaymentProofAttachment;

interface SupplierPaymentProofAttachmentWriterPort
{
    /**
     * @param list<SupplierPaymentProofAttachment> $attachments
     */
    public function createMany(array $attachments): void;
}
