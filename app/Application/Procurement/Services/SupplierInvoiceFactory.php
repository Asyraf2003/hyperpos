<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use App\Ports\Out\UuidPort;

final class SupplierInvoiceFactory
{
    public function __construct(private ProductReaderPort $products, private UuidPort $uuid) {}

    public function makeLines(array $payload): array
    {
        if ($payload === []) throw new DomainException('Invoice minimal harus memiliki satu line.');

        return array_map(function ($p) {
            $id = trim((string)($p['product_id'] ?? throw new DomainException('Product id wajib ada.')));
            if ($this->products->getById($id) === null) throw new DomainException('Product tidak ditemukan.');

            return SupplierInvoiceLine::create(
                $this->uuid->generate(),
                $id,
                (int)($p['qty_pcs'] ?? throw new DomainException('Qty wajib ada.')),
                Money::fromInt((int)($p['line_total_rupiah'] ?? throw new DomainException('Total wajib ada.')))
            );
        }, $payload);
    }
}
