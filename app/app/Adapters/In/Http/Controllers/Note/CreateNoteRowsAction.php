<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Application\Note\Services\NoteHistoryProjectionService;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Note\NoteReaderPort;

final class CreateNoteRowsAction
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly CreateNoteProductRowAction $addProductRow,
        private readonly CreateNoteServiceRowAction $addServiceRow,
        private readonly NoteHistoryProjectionService $projection,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function handle(string $noteId, array $rows): ?Result
    {
        $lineNo = $this->resolveStartingLineNo($noteId);

        if ($lineNo === null) {
            return Result::failure('Nota tidak ditemukan.', ['note' => ['NOTE_NOT_FOUND']]);
        }

        foreach ($rows as $row) {
            $result = $this->addSingleRow($noteId, $lineNo, $row);

            if ($result->isFailure()) {
                return $result;
            }

            $lineNo++;
        }

        $this->projection->syncNote($noteId);

        return null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function addSingleRow(string $noteId, int $lineNo, array $row): Result
    {
        return match ((string) ($row['line_type'] ?? '')) {
            'product' => ($this->addProductRow)($noteId, $lineNo, $row),
            'service' => ($this->addServiceRow)($noteId, $lineNo, $row),
            default => Result::failure(
                'Tipe baris nota tidak valid.',
                ['note' => ['INVALID_LINE_TYPE']]
            ),
        };
    }

    private function resolveStartingLineNo(string $noteId): ?int
    {
        $note = $this->notes->getById(trim($noteId));

        if ($note === null) {
            return null;
        }

        $maxLineNo = 0;

        foreach ($note->workItems() as $item) {
            $maxLineNo = max($maxLineNo, $item->lineNo());
        }

        return $maxLineNo + 1;
    }
}
