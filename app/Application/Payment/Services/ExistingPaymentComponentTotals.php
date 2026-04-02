<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;

final class ExistingPaymentComponentTotals
{
    /**
     * @return array<string, int>
     */
    public static function build(
        PaymentComponentAllocationReaderPort $reader,
        string $noteId,
    ): array {
        $totals = [];

        foreach ($reader->listByNoteId($noteId) as $allocation) {
            $key = self::key($allocation->componentType(), $allocation->componentRefId());
            $totals[$key] = ($totals[$key] ?? 0) + $allocation->allocatedAmountRupiah()->amount();
        }

        return $totals;
    }

    public static function key(string $componentType, string $componentRefId): string
    {
        return $componentType . '|' . $componentRefId;
    }
}
