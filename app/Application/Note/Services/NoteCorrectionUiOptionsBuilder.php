<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\ServiceDetail;
use App\Core\Note\WorkItem\WorkItem;

final class NoteCorrectionUiOptionsBuilder
{
    /**
     * @return array<string, list<array{value:string,label:string}>>
     */
    public function build(): array
    {
        return [
            'statusOptions' => [
                ['value' => WorkItem::STATUS_OPEN, 'label' => 'Belum Selesai'],
                ['value' => WorkItem::STATUS_DONE, 'label' => 'Selesai'],
                ['value' => WorkItem::STATUS_CANCELED, 'label' => 'Dibatalkan'],
            ],
            'partSourceOptions' => [
                ['value' => ServiceDetail::PART_SOURCE_NONE, 'label' => 'Tanpa Part'],
                ['value' => ServiceDetail::PART_SOURCE_CUSTOMER_OWNED, 'label' => 'Part Bawaan Pelanggan'],
            ],
        ];
    }
}
