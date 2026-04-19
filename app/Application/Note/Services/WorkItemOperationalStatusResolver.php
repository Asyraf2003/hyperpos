<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;

final class WorkItemOperationalStatusResolver
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSE = 'close';
    public const STATUS_REFUND = 'refund';

    public function resolve(int $outstandingRupiah, int $refundedRupiah): string
    {
        $this->assertNotNegative($outstandingRupiah, 'Outstanding line tidak boleh negatif.');
        $this->assertNotNegative($refundedRupiah, 'Refunded line tidak boleh negatif.');

        if ($refundedRupiah > 0) {
            return self::STATUS_REFUND;
        }

        if ($outstandingRupiah > 0) {
            return self::STATUS_OPEN;
        }

        return self::STATUS_CLOSE;
    }

    public function isOpen(int $outstandingRupiah, int $refundedRupiah): bool
    {
        return $this->resolve($outstandingRupiah, $refundedRupiah) === self::STATUS_OPEN;
    }

    public function isClose(int $outstandingRupiah, int $refundedRupiah): bool
    {
        return $this->resolve($outstandingRupiah, $refundedRupiah) === self::STATUS_CLOSE;
    }

    public function isRefund(int $outstandingRupiah, int $refundedRupiah): bool
    {
        return $this->resolve($outstandingRupiah, $refundedRupiah) === self::STATUS_REFUND;
    }

    private function assertNotNegative(int $amount, string $message): void
    {
        if ($amount < 0) {
            throw new DomainException($message);
        }
    }
}
