<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class RefundPairLimitGuard
{
    public static function assertWithinAllocated(
        Money $pairAllocated,
        Money $pairRefunded,
        Money $refundAmount,
    ): void {
        if ($pairRefunded->add($refundAmount)->greaterThan($pairAllocated)) {
            throw new DomainException('Refund melebihi total allocation untuk payment-note pair.');
        }
    }
}
