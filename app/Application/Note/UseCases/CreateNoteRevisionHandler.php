<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\NoteCurrentRevisionResolver;
use App\Application\Note\Services\NoteRevisionBootstrapFactory;
use App\Core\Note\Note\Note;
use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Note\NoteRevisionWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class CreateNoteRevisionHandler
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteCurrentRevisionResolver $currentRevision,
        private readonly NoteRevisionWriterPort $revisionWriter,
        private readonly NoteRevisionBootstrapFactory $factory,
        private readonly ClockPort $clock,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
    ) {
    }

    /**
     * @param array{
     *   note: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   reason: string
     * } $payload
     */
    public function handle(string $noteRootId, array $payload, ?string $actorId = null): CreateNoteRevisionResult
    {
        $started = false;

        try {
            $this->transactions->begin();
            $started = true;

            $root = $this->notes->getById(trim($noteRootId));

            if ($root === null) {
                return CreateNoteRevisionResult::failure('Root note tidak ditemukan.');
            }

            $current = $this->currentRevision->resolveOrFail($root->id());
            $nextRevisionNumber = $this->currentRevision->nextRevisionNumber($root->id());
            $revisionId = sprintf('%s-r%03d', $root->id(), $nextRevisionNumber);

            $revisedNote = $this->buildRevisionNoteFromPayload($root->id(), $payload);

            $revision = $this->factory->createNextRevision(
                $revisionId,
                $current->id(),
                $nextRevisionNumber,
                $revisedNote,
                $actorId,
                $this->clock->now(),
                (string) ($payload['reason'] ?? ''),
            );

            $this->revisionWriter->create($revision);
            $this->revisionWriter->setCurrentRevision(
                $root->id(),
                $revision->id(),
                $revision->revisionNumber(),
            );

            $this->audit->record('note_revision_created', [
                'note_root_id' => $root->id(),
                'revision_id' => $revision->id(),
                'revision_number' => $revision->revisionNumber(),
                'parent_revision_id' => $current->id(),
                'reason' => (string) ($payload['reason'] ?? ''),
                'actor_id' => $actorId,
                'line_count' => $revision->lineCount(),
                'grand_total_rupiah' => $revision->grandTotalRupiah(),
            ]);

            $this->transactions->commit();

            return CreateNoteRevisionResult::success([
                'note_root_id' => $root->id(),
                'revision_id' => $revision->id(),
                'revision_number' => $revision->revisionNumber(),
            ], 'Revisi nota berhasil disimpan.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return CreateNoteRevisionResult::failure($e->getMessage());
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param array{
     *   note: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   reason: string
     * } $payload
     */
    private function buildRevisionNoteFromPayload(string $noteRootId, array $payload): Note
    {
        $noteData = (array) ($payload['note'] ?? []);
        $itemsData = array_values((array) ($payload['items'] ?? []));

        $workItems = [];
        $lineNo = 1;

        foreach ($itemsData as $item) {
            if (! is_array($item)) {
                continue;
            }

            $lineType = (string) ($item['line_type'] ?? '');

            if ($lineType === 'service') {
                $serviceName = trim((string) ($item['service_name'] ?? ''));
                $servicePrice = (int) preg_replace('/\D+/', '', (string) ($item['service_price'] ?? '0'));

                $service = ServiceDetail::create(
                    $serviceName === '' ? 'Service Revision' : $serviceName,
                    Money::fromInt($servicePrice),
                    ServiceDetail::PART_SOURCE_NONE,
                );

                $workItems[] = WorkItem::createServiceOnly(
                    sprintf('%s-wi-r%03d', $noteRootId, $lineNo),
                    $noteRootId,
                    $lineNo,
                    $service,
                    WorkItem::STATUS_OPEN,
                );

                $lineNo++;
                continue;
            }

            if ($lineType === 'product') {
                $qty = max((int) preg_replace('/\D+/', '', (string) ($item['qty'] ?? '1')), 1);
                $price = (int) preg_replace('/\D+/', '', (string) ($item['price'] ?? '0'));

                $storeStockLines = [[
                    'product_id' => (string) ($item['product_id'] ?? ''),
                    'qty' => $qty,
                    'selling_price_rupiah' => $price,
                    'subtotal_rupiah' => $qty * $price,
                    'note' => (string) ($item['note'] ?? ''),
                ]];

                $workItems[] = WorkItem::createStoreStockSaleOnly(
                    sprintf('%s-wi-r%03d', $noteRootId, $lineNo),
                    $noteRootId,
                    $lineNo,
                    $storeStockLines,
                    WorkItem::STATUS_OPEN,
                );

                $lineNo++;
                continue;
            }
        }

        if ($workItems === []) {
            throw new DomainException('Minimal satu item valid wajib ada untuk membuat revisi.');
        }

        $total = array_reduce(
            $workItems,
            fn (int $carry, WorkItem $item): int => $carry + $item->subtotalRupiah()->amount(),
            0,
        );

        return Note::rehydrate(
            $noteRootId,
            (string) ($noteData['customer_name'] ?? ''),
            isset($noteData['customer_phone']) ? (string) $noteData['customer_phone'] : null,
            new \DateTimeImmutable((string) ($noteData['transaction_date'] ?? '')),
            Money::fromInt($total),
            $workItems,
            Note::STATE_OPEN,
        );
    }
}
