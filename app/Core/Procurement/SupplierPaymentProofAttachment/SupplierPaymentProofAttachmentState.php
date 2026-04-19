<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierPaymentProofAttachment;

use DateTimeImmutable;

trait SupplierPaymentProofAttachmentState
{
    private function __construct(
        private string $id,
        private string $supplierPaymentId,
        private string $storagePath,
        private string $originalFilename,
        private string $mimeType,
        private int $fileSizeBytes,
        private DateTimeImmutable $uploadedAt,
        private string $uploadedByActorId,
    ) {
    }

    public function id(): string { return $this->id; }
    public function supplierPaymentId(): string { return $this->supplierPaymentId; }
    public function storagePath(): string { return $this->storagePath; }
    public function originalFilename(): string { return $this->originalFilename; }
    public function mimeType(): string { return $this->mimeType; }
    public function fileSizeBytes(): int { return $this->fileSizeBytes; }
    public function uploadedAt(): DateTimeImmutable { return $this->uploadedAt; }
    public function uploadedByActorId(): string { return $this->uploadedByActorId; }
}
