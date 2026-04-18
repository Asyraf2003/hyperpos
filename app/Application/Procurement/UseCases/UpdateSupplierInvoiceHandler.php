<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Context\SupplierInvoiceChangeContext;
use App\Application\Procurement\Services\SupplierInvoiceEditabilityGuard;
use App\Application\Procurement\Services\UpdatedSupplierInvoiceBuilder;
use App\Application\Shared\DTO\Result;
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
        private readonly GetProcurementInvoiceDetailHandler $details,
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
            return Result::failure(
                'Nota supplier tidak ditemukan.',
                ['supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND']]
            );
        }

        $guard = $this->guard->ensureEditable($supplierInvoiceId);
        if ($guard->isFailure()) {
            return $guard;
        }

        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $this->changeContext->set(
                $performedByActorId,
                $performedByActorRole,
                $sourceChannel,
                'supplier_invoice_updated',
            );

            $updated = $this->builder->build(
                $current,
                $nomorFaktur,
                $namaPtPengirim,
                $tanggalPengiriman,
                $lines,
            );

            $totalPaidRupiah = $this->resolveTotalPaidRupiah($supplierInvoiceId);

            if ($updated->grandTotalRupiah()->amount() < $totalPaidRupiah) {
                $this->transactions->rollBack();
                $started = false;

                return Result::failure(
                    'Total revisi tidak boleh lebih kecil dari total pembayaran yang sudah tercatat.',
                    ['supplier_invoice' => ['SUPPLIER_INVOICE_REVISED_TOTAL_BELOW_TOTAL_PAID']]
                );
            }

            $this->writer->update($updated);
            $this->transactions->commit();

            return Result::success(
                ['id' => $updated->id()],
                'Nota supplier berhasil diperbarui.'
            );
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['supplier_invoice' => ['INVALID_SUPPLIER_INVOICE']]
            );
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        } finally {
            $this->changeContext->clear();
        }
    }

    private function resolveTotalPaidRupiah(string $supplierInvoiceId): int
    {
        $detail = $this->details->handle($supplierInvoiceId);
        $payload = $detail->data();

        if (! is_array($payload)) {
            return 0;
        }

        $summary = is_array($payload['summary'] ?? null) ? $payload['summary'] : [];

        return (int) ($summary['total_paid_rupiah'] ?? 0);
    }
}
