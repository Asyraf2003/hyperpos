<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierPaymentProofAttachment;

use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class SupplierPaymentProofAttachment
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

    public function id(): string
    {
        return $this->id;
    }

    public function supplierPaymentId(): string
    {
        return $this->supplierPaymentId;
    }

    public function storagePath(): string
    {
        return $this->storagePath;
    }

    public function originalFilename(): string
    {
        return $this->originalFilename;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function fileSizeBytes(): int
    {
        return $this->fileSizeBytes;
    }

    public function uploadedAt(): DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function uploadedByActorId(): string
    {
        return $this->uploadedByActorId;
    }

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
