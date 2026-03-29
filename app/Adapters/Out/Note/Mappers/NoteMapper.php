<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Mappers;

use App\Core\Note\Note\Note;
use App\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;
use stdClass;

final class NoteMapper
{
    /**
     * @param list<\App\Core\Note\WorkItem\WorkItem> $items
     */
    public static function map(stdClass $row, array $items): Note
    {
        $customerPhone = property_exists($row, 'customer_phone')
            ? ($row->customer_phone === null ? null : (string) $row->customer_phone)
            : null;

        return Note::rehydrate(
            (string) $row->id,
            (string) $row->customer_name,
            $customerPhone,
            new DateTimeImmutable((string) $row->transaction_date),
            Money::fromInt((int) $row->total_rupiah),
            $items
        );
    }
}
