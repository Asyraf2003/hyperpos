<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

trait CorrectPaidServiceOnlySupportTrait
{
    private function findWorkItem(Note $note, int $lineNo): WorkItem
    {
        foreach ($note->workItems() as $item) {
            if ($item->lineNo() === $lineNo) {
                return $item;
            }
        }

        throw new DomainException('Work item pada note tidak ditemukan.');
    }

    private function calculateRefundRequired(
        PaymentAllocationReaderPort $allocations,
        CustomerRefundReaderPort $refunds,
        string $noteId,
        Money $afterTotal
    ): int {
        $allocated = $allocations->getTotalAllocatedAmountByNoteId($noteId);
        $refunded = $refunds->getTotalRefundedAmountByNoteId($noteId);
        $netSettlement = $allocated->subtract($refunded);
        
        $netSettlement->ensureNotNegative('Net settlement pada note tidak boleh negatif.');

        if ($netSettlement->greaterThan($afterTotal)) {
            return $netSettlement->subtract($afterTotal)->amount();
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @return array<string, mixed>
     */
    private function formatAuditPayload(
        string $actorId,
        string $noteId,
        int $lineNo,
        string $reason,
        int $refundRequired,
        array $before,
        array $after
    ): array {
        return [
            'performed_by_actor_id' => trim($actorId),
            'note_id' => $noteId,
            'line_no' => $lineNo,
            'reason' => trim($reason),
            'refund_required_rupiah' => $refundRequired,
            'before' => $before,
            'after' => $after,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSuccessPayload(Note $note, WorkItem $workItem, int $refundRequired): array
    {
        return [
            'note' => [
                'id' => $note->id(),
                'total_rupiah' => $note->totalRupiah()->amount(),
            ],
            'work_item' => [
                'id' => $workItem->id(),
                'line_no' => $workItem->lineNo(),
                'transaction_type' => $workItem->transactionType(),
                'status' => $workItem->status(),
                'subtotal_rupiah' => $workItem->subtotalRupiah()->amount(),
            ],
            'refund_required_rupiah' => $refundRequired,
        ];
    }
}
