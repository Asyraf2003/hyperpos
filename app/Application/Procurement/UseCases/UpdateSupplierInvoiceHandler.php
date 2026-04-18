<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceEditabilityGuard;
use App\Application\Procurement\Services\SupplierInvoiceRevisionContextResolver;
use App\Application\Procurement\Services\SupplierInvoiceRevisionDeltaMovementsBuilder;
use App\Application\Procurement\Services\SupplierInvoiceRevisionDeltaStockGuard;
use App\Application\Procurement\Services\SupplierInvoiceRevisionInventoryEffectsApplier;
use App\Application\Procurement\Services\UpdatedSupplierInvoiceBuilder;
use App\Application\Shared\DTO\Result;
use App\Application\Procurement\Context\SupplierInvoiceChangeContext;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierInvoiceWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class UpdateSupplierInvoiceHandler
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $reader,
        private readonly SupplierInvoiceWriterPort $writer,
        private readonly UpdatedSupplierInvoiceBuilder $builder,
        private readonly SupplierInvoiceEditabilityGuard $guard,
        private readonly TransactionManagerPort $transactions,
        private readonly SupplierInvoiceChangeContext $changeContext,
        private readonly SupplierInvoiceRevisionContextResolver $contextResolver,
        private readonly SupplierInvoiceRevisionDeltaMovementsBuilder $deltaMovements,
        private readonly SupplierInvoiceRevisionDeltaStockGuard $deltaStockGuard,
        private readonly SupplierInvoiceRevisionInventoryEffectsApplier $inventoryEffects,
    ) {
    }

    public function handle(
        string $supplierInvoiceId,
        string $nomorFaktur,
        string $namaPtPengirim,
        string $tanggalPengiriman,
        array $lines,
        ?string $performedByActorId = null,
        ?string $performedByActorRole = null,
        string $sourceChannel = 'web_admin',
    ): Result {
        $current = $this->reader->getById($supplierInvoiceId);

        if ($current === null) {
            return Result::failure('Nota supplier tidak ditemukan.', ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]);
        }

        $editable = $this->guard->ensureEditable($supplierInvoiceId);
        if ($editable->isFailure()) {
            return $editable;
        }

        $started = false;

        try {
            $this->transactions->begin();
            $started = true;
            $this->changeContext->set($performedByActorId, $performedByActorRole, $sourceChannel, 'supplier_invoice_updated');

            $updated = $this->builder->build($current, $nomorFaktur, $namaPtPengirim, $tanggalPengiriman, $lines);
            $context = $this->contextResolver->resolve($supplierInvoiceId, $updated);

            if ($updated->grandTotalRupiah()->amount() < $context->totalPaidRupiah()) {
                $this->transactions->rollBack();

                return Result::failure(
                    'Total revisi tidak boleh lebih kecil dari total pembayaran yang sudah tercatat.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_REVISED_TOTAL_BELOW_TOTAL_PAID']]
                );
            }

            $deltaMovements = $context->totalReceivedQty() > 0
                ? $this->deltaMovements->build($current, $updated, $lines, $context->movementDate())
                : [];

            if (! $this->deltaStockGuard->canApplyWithoutNegativeStock($deltaMovements)) {
                $this->transactions->rollBack();

                return Result::failure(
                    'Revisi faktur akan membuat stok product lama menjadi negatif.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_REVISION_NEGATIVE_STOCK']]
                );
            }

            $this->writer->update($updated);

            $inventoryEffects = $this->inventoryEffects->apply($deltaMovements);
            if ($inventoryEffects->isFailure()) {
                $this->transactions->rollBack();
                return $inventoryEffects;
            }

            $this->transactions->commit();

            return Result::success(['id' => $updated->id()], 'Nota supplier berhasil diperbarui.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure($e->getMessage(), ['supplier_invoice' => ['INVALID_SUPPLIER_INVOICE']]);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        } finally {
            $this->changeContext->clear();
        }
    }
}
