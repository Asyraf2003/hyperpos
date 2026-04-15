<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteOperationalStatusEvaluator
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSE = 'close';

    public function resolve(int $grandTotalRupiah, int $netPaidRupiah): string
    {
        return $this->isClose($grandTotalRupiah, $netPaidRupiah)
            ? self::STATUS_CLOSE
            : self::STATUS_OPEN;
    }

    public function isOpen(int $grandTotalRupiah, int $netPaidRupiah): bool
    {
        return $this->resolve($grandTotalRupiah, $netPaidRupiah) === self::STATUS_OPEN;
    }

    public function isClose(int $grandTotalRupiah, int $netPaidRupiah): bool
    {
        if ($grandTotalRupiah <= 0) {
            return false;
        }

        return max($netPaidRupiah, 0) >= $grandTotalRupiah;
    }
}
