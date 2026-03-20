<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierPaymentProofAttachment;

use DateTimeImmutable;

final class SupplierPaymentProofAttachment
{
    use SupplierPaymentProofAttachmentState;
    use SupplierPaymentProofAttachmentValidation;

    public static function create(
        string $id,
        string $supplierPaymentId,
        string $storagePath,
        string $originalFilename,
        string $mimeType,
        int $fileSizeBytes,
        DateTimeImmutable $uploadedAt,
        string $uploadedByActorId,
    ): self {
        self::assertValid(
            $id,
            $supplierPaymentId,
            $storagePath,
            $originalFilename,
            $mimeType,
            $fileSizeBytes,
            $uploadedByActorId,
        );

        return new self(
            trim($id),
            trim($supplierPaymentId),
            trim($storagePath),
            trim($originalFilename),
            trim($mimeType),
            $fileSizeBytes,
            $uploadedAt,
            trim($uploadedByActorId),
        );
    }

    public static function rehydrate(
        string $id,
        string $supplierPaymentId,
        string $storagePath,
        string $originalFilename,
        string $mimeType,
        int $fileSizeBytes,
        DateTimeImmutable $uploadedAt,
        string $uploadedByActorId,
    ): self {
        return self::create(
            $id,
            $supplierPaymentId,
            $storagePath,
            $originalFilename,
            $mimeType,
            $fileSizeBytes,
            $uploadedAt,
            $uploadedByActorId,
        );
    }
}
