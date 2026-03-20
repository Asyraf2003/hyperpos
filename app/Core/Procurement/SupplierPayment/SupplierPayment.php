<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierPayment;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

final class SupplierPayment
{
    public const PROOF_STATUS_PENDING = 'pending';
    public const PROOF_STATUS_UPLOADED = 'uploaded';

    use SupplierPaymentState;
    use SupplierPaymentValidation;

    public static function create(
        string $id,
        string $invId,
        Money $amt,
        DateTimeImmutable $paid,
        string $status,
        ?string $path
    ): self {
        self::assertValid($id, $invId, $amt, $status, $path);

        return new self(
            trim($id),
            trim($invId),
            $amt,
            $paid,
            trim($status),
            self::normalizePath($path)
        );
    }

    public static function rehydrate(
        string $id,
        string $invId,
        Money $amt,
        DateTimeImmutable $paid,
        string $status,
        ?string $path
    ): self {
        self::assertValid($id, $invId, $amt, $status, $path);

        return new self(
            trim($id),
            trim($invId),
            $amt,
            $paid,
            trim($status),
            self::normalizePath($path)
        );
    }

    public function markProofUploaded(): void
    {
        $this->proofStatus = self::PROOF_STATUS_UPLOADED;
    }

    public function attachProof(string $path): void
    {
        $normalized = self::normalizePath($path) ?? throw new DomainException('Path wajib ada.');

        $this->proofStoragePath = $normalized;
        $this->proofStatus = self::PROOF_STATUS_UPLOADED;
    }
}
