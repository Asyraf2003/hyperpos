<?php

declare(strict_types=1);

namespace App\Application\Procurement\DTO;

final readonly class SupplierPaymentProofAttachmentFile
{
    public function __construct(
        private string $content,
        private string $mimeType,
        private string $originalFilename,
    ) {
    }

    public function content(): string
    {
        return $this->content;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function originalFilename(): string
    {
        return $this->originalFilename;
    }
}
