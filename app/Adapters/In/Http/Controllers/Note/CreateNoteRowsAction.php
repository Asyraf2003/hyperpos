<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Application\Shared\DTO\Result;

final class CreateNoteRowsAction
{
    public function __construct(
        private readonly CreateNoteProductRowAction $addProductRow,
        private readonly CreateNoteServiceRowAction $addServiceRow,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function handle(string $noteId, array $rows): ?Result
    {
        $lineNo = 1;

        foreach ($rows as $row) {
            $result = $this->addSingleRow($noteId, $lineNo, $row);

            if ($result->isFailure()) {
                return $result;
            }

            $lineNo++;
        }

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
}
