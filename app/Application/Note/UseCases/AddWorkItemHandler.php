<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Inventory\Services\IssueInventoryOperation;
use App\Application\Note\Services\AddWorkItemErrorClassifier;
use App\Application\Note\Services\WorkItemFactory;
use App\Application\Shared\DTO\Result;
use App\Core\Note\Policies\NoteAddabilityPolicy;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Note\{NoteReaderPort, NoteWriterPort, WorkItemWriterPort};
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class AddWorkItemHandler
{
    public function __construct(
        private NoteReaderPort $notes,
        private NoteWriterPort $noteWriter,
        private WorkItemWriterPort $workItemWriter,
        private IssueInventoryOperation $issueInventory,
        private TransactionManagerPort $transactions,
        private WorkItemFactory $factory,
        private NoteAddabilityPolicy $addability,
        private AddWorkItemErrorClassifier $errors
    ) {}

    public function handle(string $nId, int $lNo, string $type, array $sd, array $ext = [], array $sto = []): Result
    {
        $started = false;
        try {
            $this->transactions->begin();
            $started = true;

            $note = $this->notes->getById(trim($nId)) ?? throw new DomainException('Note tidak ditemukan.');
            
            // 1. Policy Check
            $this->addability->assertAllowed($note);

            // 2. Build via Factory
            $workItem = $this->factory->build($note->id(), $lNo, trim($type), $sd, $ext, $sto);

            // 3. Domain Interaction
            $note->addWorkItem($workItem);

            // 4. Persistence
            $this->workItemWriter->create($workItem);
            foreach ($workItem->storeStockLines() as $line) {
                $this->issueInventory->execute($line->productId(), $line->qty(), $note->transactionDate(), 'work_item_store_stock_line', $line->id());
            }
            $this->noteWriter->updateTotal($note);

            $this->transactions->commit();
            return Result::success($this->mapResponse($note, $workItem), 'Work item berhasil ditambahkan.');

        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return $this->errors->classify($e);
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }

    private function mapResponse($note, $workItem): array
    {
        return [
            'note' => ['id' => $note->id(), 'total_rupiah' => $note->totalRupiah()->amount()],
            'work_item' => ['id' => $workItem->id(), 'subtotal_rupiah' => $workItem->subtotalRupiah()->amount()]
        ];
    }
}
