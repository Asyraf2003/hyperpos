<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Procurement\SupplierListProjectionSourceReaderPort;
use App\Ports\Out\Procurement\SupplierListProjectionWriterPort;

final class SupplierListProjectionService
{
    public function __construct(
        private readonly SupplierListProjectionSourceReaderPort $source,
        private readonly SupplierListProjectionWriterPort $writer,
        private readonly ClockPort $clock,
    ) {
    }

    public function syncSupplier(string $supplierId): void
    {
        $normalizedSupplierId = trim($supplierId);

        if ($normalizedSupplierId === '') {
            throw new DomainException('Supplier id projection wajib diisi.');
        }

        $sourceRow = $this->source->findBySupplierId($normalizedSupplierId);

        if ($sourceRow === null) {
            throw new DomainException('Source projection supplier tidak ditemukan.');
        }

        $this->writer->upsert([
            ...$sourceRow,
            'projected_at' => $this->clock->now()->format('Y-m-d H:i:s'),
        ]);
    }
}
