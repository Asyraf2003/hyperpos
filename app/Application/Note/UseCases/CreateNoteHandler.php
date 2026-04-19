<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\NoteHistoryProjectionService;
use App\Application\Shared\DTO\Result;
use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Note\NoteWriterPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class CreateNoteHandler
{
    public function __construct(
        private readonly NoteWriterPort $notes,
        private readonly UuidPort $uuid,
        private readonly NoteHistoryProjectionService $projection,
    ) {
    }

    public function handle(
        string $customerName,
        ?string $customerPhone,
        string $transactionDate,
    ): Result {
        try {
            $note = Note::create(
                $this->uuid->generate(),
                trim($customerName),
                $customerPhone,
                $this->parseTransactionDate($transactionDate),
            );
        } catch (DomainException $e) {
            return Result::failure(
                $e->getMessage(),
                ['note' => ['INVALID_NOTE']]
            );
        }

        $this->notes->create($note);
        $this->projection->syncNote($note->id());

        return Result::success(
            [
                'id' => $note->id(),
                'customer_name' => $note->customerName(),
                'customer_phone' => $note->customerPhone(),
                'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                'total_rupiah' => $note->totalRupiah()->amount(),
            ],
            'Note berhasil dibuat.'
        );
    }

    private function parseTransactionDate(string $transactionDate): DateTimeImmutable
    {
        $normalized = trim($transactionDate);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new DomainException('Transaction date pada note wajib berupa tanggal yang valid dengan format Y-m-d.');
        }

        return $parsed;
    }
}
