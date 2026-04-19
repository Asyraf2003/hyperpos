<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Procurement\SupplierInvoiceListProjectionSourceReaderPort;
use App\Ports\Out\Procurement\SupplierInvoiceListProjectionWriterPort;

final class SupplierInvoiceListProjectionService
{
    public function __construct(
        private readonly SupplierInvoiceListProjectionSourceReaderPort $source,
        private readonly SupplierInvoiceListProjectionWriterPort $writer,
        private readonly ClockPort $clock,
    ) {
    }

    public function syncInvoice(string $supplierInvoiceId): void
    {
        $normalizedInvoiceId = trim($supplierInvoiceId);

        if ($normalizedInvoiceId === '') {
            throw new DomainException('Supplier invoice id projection wajib diisi.');
        }

        $sourceRow = $this->source->findBySupplierInvoiceId($normalizedInvoiceId);

        if ($sourceRow === null) {
            throw new DomainException('Source projection supplier invoice tidak ditemukan.');
        }

        $this->writer->upsert([
            ...$sourceRow,
            'payment_status' => $this->resolvePaymentStatus(
                $sourceRow['voided_at'],
                $sourceRow['outstanding_rupiah'],
            ),
            'projected_at' => $this->clock->now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function resolvePaymentStatus(?string $voidedAt, int $outstandingRupiah): string
    {
        if ($voidedAt !== null && trim($voidedAt) !== '') {
            return 'voided';
        }

        return $outstandingRupiah <= 0 ? 'paid' : 'outstanding';
    }
}
