<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\Policies\NotePaidStatusPolicy;
use App\Application\Note\UseCases\CorrectPaidServiceOnlySupportTrait;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\Note\WorkItemWriterPort;

final class CorrectPaidServiceOnlyWorkItemMutation
{
    use CorrectPaidServiceOnlySupportTrait;

    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly WorkItemWriterPort $workItems,
        private readonly NoteWriterPort $noteWriter,
        private readonly NotePaidStatusPolicy $paidStatus,
        private readonly NoteCorrectionSnapshotBuilder $snapshots,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function apply(
        string $noteId,
        int $lineNo,
        string $serviceName,
        int $servicePriceRupiah,
        string $partSource,
    ): array {
        $note = $this->notes->getById(trim($noteId)) ?? throw new DomainException('Note tidak ditemukan.');
        $this->paidStatus->assertPaidForCorrection($note);

        $target = $this->findWorkItem($note, $lineNo);
        if ($target->transactionType() !== WorkItem::TYPE_SERVICE_ONLY) {
            throw new DomainException('Correction nominal slice ini hanya mendukung work item service_only.');
        }

        $before = $this->snapshots->build($note);
        $detail = ServiceDetail::create($serviceName, Money::fromInt($servicePriceRupiah), $partSource);
        $corrected = WorkItem::rehydrate(
            $target->id(),
            $target->noteId(),
            $target->lineNo(),
            $target->transactionType(),
            $target->status(),
            $detail->servicePriceRupiah(),
            $detail,
            [],
            []
        );

        $newTotal = $note->totalRupiah()->subtract($target->subtotalRupiah())->add($corrected->subtotalRupiah());
        $newTotal->ensureNotNegative('Total note hasil correction tidak boleh negatif.');

        $note->syncTotalRupiah($newTotal);
        $this->workItems->updateServiceOnly($corrected);
        $this->noteWriter->updateTotal($note);

        $afterNote = $this->notes->getById($note->id()) ?? throw new DomainException('Note tidak ditemukan setelah correction.');

        return [
            'note' => $note,
            'before' => $before,
            'after_note' => $afterNote,
            'after' => $this->snapshots->build($afterNote),
            'corrected' => $corrected,
        ];
    }
}
