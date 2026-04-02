<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Note\Note\Note;

final class ResolveNotePayableComponents
{
    /**
     * @return list<PayableNoteComponent>
     */
    public function fromNote(Note $note): array
    {
        $components = [];
        $nextOrder = 1;

        foreach ($note->workItems() as $item) {
            $resolved = PayableComponentsFromWorkItem::resolve($item, $nextOrder);
            $components = [...$components, ...$resolved];
            $nextOrder += count($resolved);
        }

        return $components;
    }
}
