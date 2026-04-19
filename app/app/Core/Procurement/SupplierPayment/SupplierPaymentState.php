<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierPayment;

use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

trait SupplierPaymentState
{
    private function __construct(
        private string $id,
        private string $supplierInvoiceId,
        private Money $amountRupiah,
        private DateTimeImmutable $paidAt,
        private string $proofStatus,
        private ?string $proofStoragePath,
    ) {}

    public function id(): string { return $this->id; }
    public function supplierInvoiceId(): string { return $this->supplierInvoiceId; }
    public function amountRupiah(): Money { return $this->amountRupiah; }
    public function paidAt(): DateTimeImmutable { return $this->paidAt; }
    public function proofStatus(): string { return $this->proofStatus; }
    public function proofStoragePath(): ?string { return $this->proofStoragePath; }
}
