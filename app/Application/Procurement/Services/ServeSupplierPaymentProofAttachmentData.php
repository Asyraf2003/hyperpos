<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\SupplierPaymentProofAttachment\SupplierPaymentProofAttachment;
use App\Ports\Out\Procurement\SupplierPaymentProofAttachmentReaderPort;

final class ServeSupplierPaymentProofAttachmentData
{
    public function __construct(
        private readonly SupplierPaymentProofAttachmentReaderPort $attachments,
    ) {
    }

    public function getById(string $attachmentId): ?SupplierPaymentProofAttachment
    {
        return $this->attachments->getById(trim($attachmentId));
    }
}
