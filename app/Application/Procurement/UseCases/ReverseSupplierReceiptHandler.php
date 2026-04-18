<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceRevisionDeltaStockGuard;
use App\Application\Procurement\Services\SupplierInvoiceRevisionInventoryEffectsApplier;
use App\Application\Procurement\Services\SupplierReceiptReversalDeltaMovementsBuilder;
use App\Application\Procurement\Services\SupplierReceiptReversalPreflight;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\{AuditLogPort, TransactionManagerPort, UuidPort};
use Illuminate\Support\Facades\DB;
use Throwable;

final class ReverseSupplierReceiptHandler
{
    public function __construct(
        private readonly SupplierReceiptReversalPreflight $preflight,
        private readonly SupplierReceiptReversalDeltaMovementsBuilder $deltaMovements,
        private readonly SupplierInvoiceRevisionDeltaStockGuard $deltaStockGuard,
        private readonly SupplierInvoiceRevisionInventoryEffectsApplier $inventoryEffects,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
        private readonly UuidPort $uuid,
    ) {
    }

    public function handle(string $supplierReceiptId, string $reversedAt, string $reason, string $actorId): Result
    {
        $started = false;

        try {
            $prepared = $this->preflight->prepare($supplierReceiptId, $reversedAt, $actorId);

            if ($prepared->isFailure()) {
                return $prepared;
            }

            $data = $prepared->data();
            $date = $data['reversed_at'];

            $deltaMovements = $this->deltaMovements->build((string) $data['supplier_receipt_id'], $date);

            if (! $this->deltaStockGuard->canApplyWithoutNegativeStock($deltaMovements)) {
                return Result::failure(
                    'Reversal penerimaan supplier akan membuat stok product menjadi negatif.',
                    ['supplier_receipt_reversal' => ['SUPPLIER_RECEIPT_REVERSAL_NEGATIVE_STOCK']]
                );
            }

            $this->transactions->begin();
            $started = true;

            $reversalId = $this->uuid->generate();

            DB::table('supplier_receipt_reversals')->insert([
                'id' => $reversalId,
                'supplier_receipt_id' => (string) $data['supplier_receipt_id'],
                'reason' => trim($reason),
                'performed_by_actor_id' => (string) $data['actor_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $effects = $this->inventoryEffects->apply($deltaMovements);

            if ($effects->isFailure()) {
                return Result::failure(
                    'Proyeksi inventory gagal diperbarui setelah reversal penerimaan supplier.',
                    ['supplier_receipt_reversal' => ['SUPPLIER_RECEIPT_REVERSAL_INVENTORY_REBUILD_FAILED']]
                );
            }

            $this->audit->record('supplier_receipt_reversed', [
                'reversal_id' => $reversalId,
                'supplier_receipt_id' => (string) $data['supplier_receipt_id'],
                'supplier_invoice_id' => (string) $data['supplier_invoice_id'],
                'reason' => trim($reason),
                'reversed_at' => $date->format('Y-m-d'),
                'performed_by_actor_id' => (string) $data['actor_id'],
                'delta_movement_count' => count($deltaMovements),
            ]);

            $this->transactions->commit();

            return Result::success(['id' => $reversalId], 'Reversal penerimaan supplier berhasil dicatat.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure(
                $e->getMessage(),
                ['supplier_receipt_reversal' => ['INVALID_SUPPLIER_RECEIPT_REVERSAL']]
            );
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
