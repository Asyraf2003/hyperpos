<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;

final class ResolveNotePayableComponentsSelectedRows
{
    public function __construct(
        private readonly ResolveNotePayableComponentsSelectionId $ids,
    ) {
    }

    /**
     * @param list<string> $selectedRowIds
     * @return list<PayableNoteComponent>
     */
    public function resolve(Note $note, array $selectedRowIds): array
    {
        $selectedIds = $this->ids->normalize($selectedRowIds);

        if ($selectedIds === []) {
            throw new DomainException('Pilih minimal satu billing row outstanding untuk pembayaran.');
        }

        $components = [];
        $nextOrder = 1;

        foreach ($note->workItems() as $item) {
            $resolved = PayableComponentsFromWorkItem::resolve($item, $nextOrder);

            foreach ($resolved as $component) {
                if (in_array($this->ids->fromComponent($component), $selectedIds, true)) {
                    $components[] = $component;
                }
            }

            $nextOrder += count($resolved);
        }

        $matchedIds = array_map(
            fn (PayableNoteComponent $component): string => $this->ids->fromComponent($component),
            $components,
        );

        if (array_values(array_diff($selectedIds, $matchedIds)) !== []) {
            throw new DomainException('Billing row pembayaran yang dipilih tidak valid untuk nota ini.');
        }

        if ($components === []) {
            throw new DomainException('Billing row pembayaran yang dipilih tidak memiliki komponen yang bisa dibayar.');
        }

        return $components;
    }
}
