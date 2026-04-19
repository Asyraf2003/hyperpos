<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\WorkItem\ExternalPurchaseLine;
use App\Core\Shared\ValueObjects\Money;

final class ExternalPurchaseLinesSubtotal
{
    /**
     * @param list<ExternalPurchaseLine> $lines
     */
    public static function sum(array $lines): Money
    {
        $subtotal = Money::zero();
        foreach ($lines as $line) {
            $subtotal = $subtotal->add($line->lineTotalRupiah());
        }

        return $subtotal;
    }
}
