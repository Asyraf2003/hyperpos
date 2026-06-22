<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\CreateTransactionWorkspaceAuditPayloadBuilder;
use App\Application\Note\Services\CreateTransactionWorkspaceInlinePaymentRecorder;
use App\Application\Note\Services\CreateTransactionWorkspaceIdempotencyService;
use App\Application\Note\Services\CreateTransactionWorkspaceNoteFactory;
use App\Application\Note\Services\CreateTransactionWorkspaceResultBuilder;
use App\Application\Note\Services\CreateTransactionWorkspaceWorkItemPersister;
use App\Application\Note\Services\CreateTransactionWorkspaceInitialRevisionBootstrapper;
use App\Application\Note\Services\NoteHistoryProjectionService;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class CreateTransactionWorkspaceHandler
{
    public function __construct(
        private readonly NoteWriterPort $notes,
        private readonly CreateTransactionWorkspaceIdempotencyService $idempotency,
        private readonly TransactionManagerPort $transactions,
        private readonly CreateTransactionWorkspaceNoteFactory $noteFactory,
        private readonly CreateTransactionWorkspaceWorkItemPersister $items,
        private readonly CreateTransactionWorkspaceInlinePaymentRecorder $payments,
        private readonly CreateTransactionWorkspaceAuditPayloadBuilder $auditPayloads,
        private readonly CreateTransactionWorkspaceResultBuilder $results,
        private readonly AuditLogPort $audit,
        private readonly NoteHistoryProjectionService $projection,
        private readonly CreateTransactionWorkspaceInitialRevisionBootstrapper $initialRevision,
    ) {
    }

    public function handle(array $payload): Result
    {
        $started = false;
        $replayed = $this->idempotency->replay($payload);

        if ($replayed !== null) {
            return $replayed;
        }

        try {
            $this->transactions->begin();
            $started = true;
            $this->idempotency->start($payload);

            $note = $this->noteFactory->make((array) ($payload['note'] ?? []));
            $this->notes->create($note);

            $persistedItems = $this->items->persist($note, $payload['items'] ?? []);
            $this->notes->updateTotal($note);

            $this->initialRevision->bootstrap(
                $note,
                is_string($payload['_actor_id'] ?? null) ? $payload['_actor_id'] : null,
            );

            $paymentSummary = $this->payments->record($note, $payload['inline_payment'] ?? []);
            $this->audit->record(
                'transaction_workspace_created',
                $this->auditPayloads->build(
                    $note,
                    $persistedItems->itemsCount(),
                    $paymentSummary,
                    $persistedItems->packageAllocations()
                )
            );

            $this->projection->syncNote($note->id());

            $result = $this->results->build($note, $paymentSummary);

            $this->idempotency->succeed($payload, $result);
            $this->transactions->commit();

            return $result;
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return Result::failure($e->getMessage(), ['note' => ['INVALID_WORKSPACE']]);
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }
}
