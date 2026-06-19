<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

final class SupplierPaymentProofStoredFilenameFactory
{
    public static function make(string $originalFilename): string
    {
        $extension = preg_replace(
            '/[^a-z0-9]/',
            '',
            strtolower((string) pathinfo($originalFilename, PATHINFO_EXTENSION)),
        ) ?? '';

        return bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . $extension : '');
    }
}
