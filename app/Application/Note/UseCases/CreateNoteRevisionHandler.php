<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\NoteCurrentRevisionResolver;
use App\Application\Note\Services\NoteRevisionBootstrapFactory;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class CreateNoteRevisionHandler
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly NoteCurrentRevisionResolver $currentRevision,
        private readonly NoteRevisionBootstrapFactory $factory,
        private readonly CreateNoteRevisionPayloadNoteBuilder $notesFromPayload,
        private readonly CreateNoteRevisionCommitter $committer,
        private readonly ClockPort $clock,
        private readonly TransactionManagerPort $transactions,
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
            $reason = (string) ($payload['reason'] ?? '');

            $revision = $this->factory->createNextRevision(
                sprintf('%s-r%03d', $root->id(), $nextRevisionNumber),
                $current->id(),
                $nextRevisionNumber,
                $this->notesFromPayload->build($root->id(), $payload),
                $actorId,
                $this->clock->now(),
                $reason,
            );

            $result = $this->committer->commit(
                $root->id(),
                $current->id(),
                $actorId,
                $reason,
                $revision,
            );

            $this->transactions->commit();

            return $result;
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
}
