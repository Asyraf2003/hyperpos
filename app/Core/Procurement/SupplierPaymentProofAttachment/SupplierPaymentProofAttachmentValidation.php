<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierPaymentProofAttachment;

use App\Core\Shared\Exceptions\DomainException;

trait SupplierPaymentProofAttachmentValidation
{
    private static function assertValid(
        string $id,
        string $supplierPaymentId,
        string $storagePath,
        string $originalFilename,
        string $mimeType,
        int $fileSizeBytes,
        string $uploadedByActorId,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('ID lampiran bukti pembayaran supplier wajib ada.');
        }

        if (trim($supplierPaymentId) === '') {
            throw new DomainException('Supplier payment ID wajib ada.');
        }

        if (trim($storagePath) === '') {
            throw new DomainException('Storage path bukti pembayaran wajib ada.');
        }

        if (trim($originalFilename) === '') {
            throw new DomainException('Nama file asli bukti pembayaran wajib ada.');
        }

        if (trim($mimeType) === '') {
            throw new DomainException('Mime type bukti pembayaran wajib ada.');
        }

        if ($fileSizeBytes < 1) {
            throw new DomainException('Ukuran file bukti pembayaran wajib lebih dari 0 byte.');
        }

        if (trim($uploadedByActorId) === '') {
            throw new DomainException('Actor upload bukti pembayaran supplier wajib ada.');
        }
    }
}
