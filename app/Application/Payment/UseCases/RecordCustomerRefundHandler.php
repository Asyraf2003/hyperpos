<?php

declare(strict_types=1);

namespace App\Application\Payment\UseCases;

use App\Application\Inventory\Services\AutoReverseRefundedStoreStockInventory;
use App\Application\Note\Services\AutoRefundNoteWhenFullyRefunded;
use App\Application\Payment\Services\RecordCustomerRefundOperation;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class RecordCustomerRefundHandler
{
    use RecordCustomerRefundSupportTrait;

    public function __construct(
        private readonly RecordCustomerRefundOperation $operation,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
        private readonly AutoRefundNoteWhenFullyRefunded $refundLifecycle,
        private readonly AutoReverseRefundedStoreStockInventory $reverseRefundedInventory,
    ) {
    }

    /** @param list<string> $selectedRowIds */
    public function handle(
        string $customerPaymentId,
        string $noteId,
        int $amountRupiah,
        string $refundedAt,
        string $reason,
        string $performedByActorId,
        array $selectedRowIds = [],
    ): Result {
        if (trim($reason) === '') {
            return Result::failure('Alasan refund wajib diisi.', ['refund' => ['AUDIT_REASON_REQUIRED']]);
        }

        if (trim($performedByActorId) === '') {
            return $this->classify(new DomainException('Actor refund wajib ada.'));
        }

        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $recorded = $this->operation->execute(
                $customerPaymentId,
                $noteId,
                $amountRupiah,
                $refundedAt,
                $reason,
                $selectedRowIds,
            );

            $refund = $recorded->refund();

            $this->reverseRefundedInventory->execute($refund);

            $this->refundLifecycle->refundIfEligible(
                $noteId,
                $performedByActorId,
                'kasir',
                $reason,
                $customerPaymentId,
                $refund->id(),
            );

            $this->audit->record('customer_refund_recorded', array_merge(
                $this->formatAuditPayload($refund, $performedByActorId),
                ['refund_allocation_count' => $recorded->allocationCount(), 'selected_row_ids' => $selectedRowIds],
            ));

            $this->transactions->commit();

            return Result::success(
                array_merge($this->formatSuccessPayload($refund), ['refund_allocation_count' => $recorded->allocationCount()]),
                'Customer refund berhasil dicatat.'
            );
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return $this->classify($e);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
