<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\DTO\SupplierPaymentProofAttachmentFile;
use App\Application\Procurement\Services\ServeSupplierPaymentProofAttachmentData;
use App\Ports\Out\Procurement\SupplierPaymentProofFileStoragePort;

final class GetSupplierPaymentProofAttachmentFileHandler
{
    public function __construct(
        private readonly ServeSupplierPaymentProofAttachmentData $attachments,
        private readonly SupplierPaymentProofFileStoragePort $files,
    ) {
    }

    public function handle(string $attachmentId): ?SupplierPaymentProofAttachmentFile
    {
        $attachment = $this->attachments->getById($attachmentId);

        if ($attachment === null) {
            return null;
        }

        $content = $this->files->get($attachment->storagePath());

        if ($content === null) {
            return null;
        }

        return new SupplierPaymentProofAttachmentFile(
            $content,
            $attachment->mimeType(),
            $attachment->originalFilename(),
        );
    }
}
