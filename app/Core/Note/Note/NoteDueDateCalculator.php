<?php

declare(strict_types=1);

namespace App\Core\Note\Note;

use DateTimeImmutable;

final class NoteDueDateCalculator
{
    public static function calculate(DateTimeImmutable $transactionDate): DateTimeImmutable
    {
        $month = (int) $transactionDate->format('n') + 1;
        $year = (int) $transactionDate->format('Y');

        if ($month > 12) {
            $month = 1;
            $year++;
        }

        $lastDay = (int) (new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))
            ->modify('last day of this month')
            ->format('j');

        return $transactionDate->setDate($year, $month, min((int) $transactionDate->format('j'), $lastDay));
    }
}
