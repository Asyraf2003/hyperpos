<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;

final class ResolveNotePayableComponentsSelectionId
{
    public function fromComponent(PayableNoteComponent $component): string
    {
        return sprintf(
            '%s::%s::%s',
            $component->workItemId(),
            $component->componentType(),
            $component->componentRefId(),
        );
    }

    /**
     * @param list<string> $selectedRowIds
     * @return list<string>
     */
    public function normalize(array $selectedRowIds): array
    {
        $normalized = [];

        foreach ($selectedRowIds as $id) {
            $trimmed = trim($id);

            if ($trimmed !== '') {
                $normalized[] = $trimmed;
            }
        }

        return array_values(array_unique($normalized));
    }
}
