<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\Supplier\Supplier;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\{SupplierReaderPort, SupplierWriterPort};
use App\Ports\Out\UuidPort;

final class SupplierService
{
    public function __construct(
        private SupplierReaderPort $suppliers,
        private SupplierWriterPort $writer,
        private UuidPort $uuid
    ) {}

    public function resolve(string $rawName): Supplier
    {
        $normalized = trim($rawName);
        if ($normalized === '') throw new DomainException('Nama PT pengirim wajib ada.');
        
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $searchKey = mb_strtolower($normalized);

        $existing = $this->suppliers->getByNormalizedNamaPtPengirim($searchKey);
        if ($existing !== null) return $existing;

        $new = Supplier::create($this->uuid->generate(), $normalized);
        $this->writer->create($new);

        return $new;
    }
}
