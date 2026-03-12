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

    private function __construct(
        private string $id,
        private string $supplierInvoiceId,
        private Money $amountRupiah,
        private DateTimeImmutable $paidAt,
        private string $proofStatus,
        private ?string $proofStoragePath,
    ) {
    }

    public static function create(
        string $id,
        string $supplierInvoiceId,
        Money $amountRupiah,
        DateTimeImmutable $paidAt,
        string $proofStatus,
        ?string $proofStoragePath,
    ): self {
        self::assertValid(
            $id,
            $supplierInvoiceId,
            $amountRupiah,
            $proofStatus,
            $proofStoragePath,
        );

        return new self(
            trim($id),
            trim($supplierInvoiceId),
            $amountRupiah,
            $paidAt,
            $proofStatus,
            self::normalizeProofStoragePath($proofStoragePath),
        );
    }

    public static function rehydrate(
        string $id,
        string $supplierInvoiceId,
        Money $amountRupiah,
        DateTimeImmutable $paidAt,
        string $proofStatus,
        ?string $proofStoragePath,
    ): self {
        self::assertValid(
            $id,
            $supplierInvoiceId,
            $amountRupiah,
            $proofStatus,
            $proofStoragePath,
        );

        return new self(
            trim($id),
            trim($supplierInvoiceId),
            $amountRupiah,
            $paidAt,
            $proofStatus,
            self::normalizeProofStoragePath($proofStoragePath),
        );
    }

    public function attachProof(string $proofStoragePath): void
    {
        $normalizedPath = self::normalizeProofStoragePath($proofStoragePath);

        if ($normalizedPath === null) {
            throw new DomainException('Proof storage path wajib ada.');
        }

        $this->proofStoragePath = $normalizedPath;
        $this->proofStatus = self::PROOF_STATUS_UPLOADED;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function supplierInvoiceId(): string
    {
        return $this->supplierInvoiceId;
    }

    public function amountRupiah(): Money
    {
        return $this->amountRupiah;
    }

    public function paidAt(): DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function proofStatus(): string
    {
        return $this->proofStatus;
    }

    public function proofStoragePath(): ?string
    {
        return $this->proofStoragePath;
    }

    private static function assertValid(
        string $id,
        string $supplierInvoiceId,
        Money $amountRupiah,
        string $proofStatus,
        ?string $proofStoragePath,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Supplier payment id wajib ada.');
        }

        if (trim($supplierInvoiceId) === '') {
            throw new DomainException('Supplier invoice id pada supplier payment wajib ada.');
        }

        if ($amountRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Amount rupiah pada supplier payment harus lebih besar dari nol.');
        }

        if (in_array($proofStatus, self::allowedProofStatuses(), true) === false) {
            throw new DomainException('Proof status pada supplier payment tidak valid.');
        }

        $normalizedProofStoragePath = self::normalizeProofStoragePath($proofStoragePath);

        if ($proofStatus === self::PROOF_STATUS_PENDING && $normalizedProofStoragePath !== null) {
            throw new DomainException('Proof storage path harus kosong saat proof status pending.');
        }

        if ($proofStatus === self::PROOF_STATUS_UPLOADED && $normalizedProofStoragePath === null) {
            throw new DomainException('Proof storage path wajib ada saat proof status uploaded.');
        }
    }

    /**
     * @return list<string>
     */
    private static function allowedProofStatuses(): array
    {
        return [
            self::PROOF_STATUS_PENDING,
            self::PROOF_STATUS_UPLOADED,
        ];
    }

    private static function normalizeProofStoragePath(?string $proofStoragePath): ?string
    {
        if ($proofStoragePath === null) {
            return null;
        }

        $normalized = trim($proofStoragePath);

        return $normalized === '' ? null : $normalized;
    }
}
