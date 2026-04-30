<?php

declare(strict_types=1);

namespace App\Core\Payment\CustomerPayment;

use App\Core\Shared\Exceptions\DomainException;

final class CustomerPaymentMethod
{
    public const CASH = 'cash';
    public const TRANSFER = 'tf';
    public const UNKNOWN = 'unknown';

    public static function normalize(string $paymentMethod): string
    {
        $normalized = trim($paymentMethod);

        if ($normalized === 'transfer') {
            return self::TRANSFER;
        }

        return $normalized === '' ? self::UNKNOWN : $normalized;
    }

    public static function assertValid(string $paymentMethod): void
    {
        if (! in_array($paymentMethod, self::allowed(), true)) {
            throw new DomainException('Metode pembayaran customer payment tidak valid.');
        }
    }

    /**
     * @return list<string>
     */
    private static function allowed(): array
    {
        return [
            self::CASH,
            self::TRANSFER,
            self::UNKNOWN,
        ];
    }
}
