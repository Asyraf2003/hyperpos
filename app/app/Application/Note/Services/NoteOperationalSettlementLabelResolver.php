<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteOperationalSettlementLabelResolver
{
    public function resolve(int $subtotal, int $netPaid): string
    {
        if ($subtotal <= 0 || $netPaid <= 0) {
            return 'hutang';
        }

        if ($netPaid >= $subtotal) {
            return 'lunas';
        }

        return 'dp';
    }
}
