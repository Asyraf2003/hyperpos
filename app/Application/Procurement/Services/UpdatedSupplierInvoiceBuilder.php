<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class UpdatedSupplierInvoiceBuilder
{
    public function __construct(
        private readonly SupplierService $supplierService,
        private readonly SupplierInvoiceFactory $invoiceFactory,
    ) {
    }

    public function build(
        SupplierInvoice $current,
        string $nomorFaktur,
        string $namaPtPengirim,
        string $tanggalPengiriman,
        array $lines,
    ): SupplierInvoice {
        $shipmentDate = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tanggalPengiriman))
            ?: throw new DomainException('Format tanggal pengiriman salah.');

        $supplier = $this->supplierService->resolve($namaPtPengirim);
        $invoiceLines = $this->invoiceFactory->makeLines($lines);

        return SupplierInvoice::create(
            $current->id(),
            $supplier->id(),
            $supplier->namaPtPengirim(),
            trim($nomorFaktur),
            $shipmentDate,
            $invoiceLines,
        );
    }
}
