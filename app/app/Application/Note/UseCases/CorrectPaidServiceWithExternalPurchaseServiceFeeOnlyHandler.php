<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Policies\NotePaidStatusPolicy;
use App\Application\Note\Services\ExternalPurchaseLinesSubtotal;
use App\Application\Note\Services\FinalizePaidNoteCorrection;
use App\Application\Note\Services\NoteCorrectionSnapshotBuilder;
use App\Application\Shared\DTO\Result;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\Note\WorkItemWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class CorrectPaidServiceWithExternalPurchaseServiceFeeOnlyHandler
{
    use CorrectPaidServiceOnlySupportTrait;

    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly WorkItemWriterPort $workItems,
        private readonly NoteWriterPort $noteWriter,
        private readonly TransactionManagerPort $transactions,
        private readonly NotePaidStatusPolicy $paidStatus,
        private readonly NoteCorrectionSnapshotBuilder $snapshots,
        private readonly FinalizePaidNoteCorrection $finalize,
    ) {
    }

    public function handle(string $noteId, int $lineNo, string $serviceName, int $servicePriceRupiah, string $partSource, string $reason, string $performedByActorId): Result
    {
        $started = false;

        try {
            if ($lineNo <= 0) throw new DomainException('Line number harus > 0.');
            if (trim($reason) === '') return Result::failure('Alasan correction wajib diisi.', ['correction' => ['AUDIT_REASON_REQUIRED']]);
            if (trim($performedByActorId) === '') throw new DomainException('Actor correction wajib ada.');

            $this->transactions->begin();
            $started = true;
            $note = $this->notes->getById(trim($noteId)) ?? throw new DomainException('Note tidak ditemukan.');
            $this->paidStatus->assertPaidForCorrection($note);
            $target = $this->findWorkItem($note, $lineNo);

            if ($target->transactionType() !== WorkItem::TYPE_SERVICE_WITH_EXTERNAL_PURCHASE) throw new DomainException('Correction slice ini hanya mendukung work item service_with_external_purchase.');

            $before = $this->snapshots->build($note);
            $detail = ServiceDetail::create($serviceName, Money::fromInt($servicePriceRupiah), $partSource);
            $subtotal = $detail->servicePriceRupiah()->add(ExternalPurchaseLinesSubtotal::sum($target->externalPurchaseLines()));
            $corrected = WorkItem::rehydrate(
                $target->id(),
                $target->noteId(),
                $target->lineNo(),
                $target->transactionType(),
                $target->status(),
                $subtotal,
                $detail,
                $target->externalPurchaseLines(),
                []
            );

            $newTotal = $note->totalRupiah()->subtract($target->subtotalRupiah())->add($corrected->subtotalRupiah());
            $newTotal->ensureNotNegative('Total note hasil correction tidak boleh negatif.');
            $note->syncTotalRupiah($newTotal);

            $this->workItems->updateServiceWithExternalPurchaseServiceFeeOnly($corrected);
            $this->noteWriter->updateTotal($note);

            $result = $this->finalize->complete(
                $note->id(),
                $lineNo,
                'paid_service_with_external_purchase_service_fee_only_corrected',
                $performedByActorId,
                $reason,
                $before,
                $corrected,
                'Correction service_with_external_purchase service fee only berhasil disimpan.',
            );

            $this->transactions->commit();

            return $result;
        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return Result::failure($e->getMessage(), ['work_item' => ['INVALID_WORK_ITEM_STATE']]);
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }
}
