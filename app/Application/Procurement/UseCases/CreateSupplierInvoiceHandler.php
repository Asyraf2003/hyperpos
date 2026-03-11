<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Procurement\Supplier\Supplier;
use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\Procurement\SupplierReaderPort;
use App\Ports\Out\Procurement\SupplierWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class CreateSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierReaderPort $suppliers,
        private readonly SupplierWriterPort $supplierWriter,
        private readonly SupplierInvoiceWriterPort $supplierInvoices,
        private readonly ProductReaderPort $products,
        private readonly TransactionManagerPort $transactions,
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     */
    public function handle(
        string $namaPtPengirim,
        string $tanggalPengiriman,
        array $lines,
    ): Result {
        try {
            $shipmentDate = $this->parseTanggalPengiriman($tanggalPengiriman);
            $normalizedNamaPtPengirim = $this->normalizeNamaPtPengirim($namaPtPengirim);
            $invoiceLines = $this->buildInvoiceLines($lines);
        } catch (DomainException $e) {
            return Result::failure(
                $e->getMessage(),
                ['supplier_invoice' => ['INVALID_SUPPLIER_INVOICE']]
            );
        }

        $transactionStarted = false;

        try {
            $this->transactions->begin();
            $transactionStarted = true;

            $supplier = $this->resolveSupplier($namaPtPengirim, $normalizedNamaPtPengirim);

            $supplierInvoice = SupplierInvoice::create(
                $this->uuid->generate(),
                $supplier->id(),
                $shipmentDate,
                $invoiceLines,
            );

            $this->supplierInvoices->create($supplierInvoice);

            $this->transactions->commit();

            return Result::success(
                [
                    'id' => $supplierInvoice->id(),
                    'supplier_id' => $supplierInvoice->supplierId(),
                    'nama_pt_pengirim' => $supplier->namaPtPengirim(),
                    'tanggal_pengiriman' => $supplierInvoice->tanggalPengiriman()->format('Y-m-d'),
                    'jatuh_tempo' => $supplierInvoice->jatuhTempo()->format('Y-m-d'),
                    'grand_total_rupiah' => $supplierInvoice->grandTotalRupiah()->amount(),
                    'lines' => array_map(
                        static fn (SupplierInvoiceLine $line): array => [
                            'id' => $line->id(),
                            'product_id' => $line->productId(),
                            'qty_pcs' => $line->qtyPcs(),
                            'line_total_rupiah' => $line->lineTotalRupiah()->amount(),
                            'unit_cost_rupiah' => $line->unitCostRupiah()->amount(),
                        ],
                        $supplierInvoice->lines(),
                    ),
                ],
                'Supplier invoice berhasil dibuat.'
            );
        } catch (DomainException $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['supplier_invoice' => ['INVALID_SUPPLIER_INVOICE']]
            );
        } catch (Throwable $e) {
            if ($transactionStarted) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     * @return list<SupplierInvoiceLine>
     */
    private function buildInvoiceLines(array $lines): array
    {
        if ($lines === []) {
            throw new DomainException('Supplier invoice minimal harus memiliki satu line.');
        }

        $invoiceLines = [];

        foreach ($lines as $line) {
            if (array_key_exists('product_id', $line) === false) {
                throw new DomainException('Product id pada supplier invoice line wajib ada.');
            }

            if (array_key_exists('qty_pcs', $line) === false) {
                throw new DomainException('Qty pcs pada supplier invoice line wajib ada.');
            }

            if (array_key_exists('line_total_rupiah', $line) === false) {
                throw new DomainException('Line total rupiah pada supplier invoice line wajib ada.');
            }

            $productId = trim((string) $line['product_id']);
            $qtyPcs = (int) $line['qty_pcs'];
            $lineTotalRupiah = (int) $line['line_total_rupiah'];

            if ($this->products->getById($productId) === null) {
                throw new DomainException('Product pada supplier invoice line tidak ditemukan.');
            }

            $invoiceLines[] = SupplierInvoiceLine::create(
                $this->uuid->generate(),
                $productId,
                $qtyPcs,
                Money::fromInt($lineTotalRupiah),
            );
        }

        return $invoiceLines;
    }

    private function resolveSupplier(
        string $namaPtPengirim,
        string $normalizedNamaPtPengirim,
    ): Supplier {
        $existingSupplier = $this->suppliers->getByNormalizedNamaPtPengirim($normalizedNamaPtPengirim);

        if ($existingSupplier !== null) {
            return $existingSupplier;
        }

        $supplier = Supplier::create(
            $this->uuid->generate(),
            $namaPtPengirim,
        );

        $this->supplierWriter->create($supplier);

        return $supplier;
    }

    private function parseTanggalPengiriman(string $tanggalPengiriman): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', trim($tanggalPengiriman));

        if ($parsed === false || $parsed->format('Y-m-d') !== trim($tanggalPengiriman)) {
            throw new DomainException('Tanggal pengiriman wajib berupa tanggal yang valid dengan format Y-m-d.');
        }

        return $parsed;
    }

    private function normalizeNamaPtPengirim(string $namaPtPengirim): string
    {
        $normalized = trim($namaPtPengirim);

        if ($normalized === '') {
            throw new DomainException('Nama PT pengirim wajib ada.');
        }

        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return mb_strtolower($normalized);
    }
}
