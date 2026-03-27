<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NotePaymentStatusResolver
{
    public function resolve(int $grandTotalRupiah, int $netPaidRupiah): string
    {
        if ($grandTotalRupiah <= 0 || $netPaidRupiah <= 0) {
            return 'unpaid';
        }

        if ($netPaidRupiah >= $grandTotalRupiah) {
            return 'paid';
        }

        return 'partial';
    }
}
