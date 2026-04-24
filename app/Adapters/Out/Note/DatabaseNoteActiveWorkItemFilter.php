<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Core\Note\WorkItem\WorkItem;
use stdClass;

final class DatabaseNoteActiveWorkItemFilter
{
    /**
     * @param list<stdClass> $rows
     * @return list<stdClass>
     */
    public function filter(array $rows): array
    {
        return array_values(array_filter(
            $rows,
            static fn (stdClass $row): bool => ((string) ($row->status ?? '')) !== WorkItem::STATUS_CANCELED,
        ));
    }
}
