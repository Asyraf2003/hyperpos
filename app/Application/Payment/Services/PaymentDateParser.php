<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class PaymentDateParser
{
    public static function parseYmd(string $value, string $message): DateTimeImmutable
    {
        $normalized = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new DomainException($message);
        }

        return $parsed;
    }
}
