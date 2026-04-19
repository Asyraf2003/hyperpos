<?php

declare(strict_types=1);

namespace App\Application\Payment\UseCases;

use App\Application\Note\Services\NoteHistoryProjectionService;
use App\Application\Payment\Services\AllocatePaymentErrorClassifier;
use App\Application\Payment\Services\RecordAndAllocateNotePaymentOperation;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class RecordAndAllocateNotePaymentHandler
{
    public function __construct(
        private readonly RecordAndAllocateNotePaymentOperation $operation,
        private readonly TransactionManagerPort $transactions,
        private readonly AllocatePaymentErrorClassifier $errors,
        private readonly AuditLogPort $audit,
        private readonly NoteHistoryProjectionService $projection,
    ) {
    }

    /**
     * @param list<string> $selectedRowIds
     */
    public function handle(string $noteId, int $amountRupiah, string $paidAt, array $selectedRowIds = []): Result
    {
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $recorded = $this->operation->execute($noteId, $amountRupiah, $paidAt, $selectedRowIds);

            $this->audit->record('payment_allocated', [
                'payment_id' => $recorded->payment()->id(),
                'note_id' => trim($noteId),
                'amount' => $amountRupiah,
                'allocation_count' => $recorded->allocationCount(),
                'selected_row_ids' => $selectedRowIds,
            ]);

            $this->projection->syncNote(trim($noteId));

            $this->transactions->commit();

            return Result::success([
                'payment_id' => $recorded->payment()->id(),
                'allocation_count' => $recorded->allocationCount(),
            ], 'Pembayaran berhasil dicatat.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return $this->errors->classify($e);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
