<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NotePaymentStatusResolver
{
    public function resolve(int $grandTotalRupiah, int $netSettlementRupiah): string
    {
        if ($grandTotalRupiah <= 0 || $netSettlementRupiah <= 0) {
            return 'unpaid';
        }

        if ($netSettlementRupiah >= $grandTotalRupiah) {
            return 'paid';
        }

        return 'partial';
    }
}
