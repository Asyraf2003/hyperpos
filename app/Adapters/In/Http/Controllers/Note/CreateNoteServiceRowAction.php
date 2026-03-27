<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Application\Note\UseCases\AddWorkItemHandler;
use App\Application\Shared\DTO\Result;
use App\Core\Note\WorkItem\WorkItem;

final class CreateNoteServiceRowAction
{
    public function __construct(
        private readonly AddWorkItemHandler $addWorkItem,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public function __invoke(string $noteId, int $lineNo, array $row): Result
    {
        return $this->addWorkItem->handle(
            $noteId,
            $lineNo,
            WorkItem::TYPE_SERVICE_ONLY,
            [
                'service_name' => (string) ($row['service_name'] ?? ''),
                'service_price_rupiah' => (int) ($row['service_price_rupiah'] ?? 0),
            ],
            [],
            []
        );
    }
}
