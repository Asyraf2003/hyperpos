<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note\Support;

final class NotePaymentRedirectMessageBuilder
{
    /**
     * @param array<string, mixed> $data
     */
    public function success(array $data, int $amount): string
    {
        if (($data['payment_method'] ?? '') !== 'cash') {
            return 'Pembayaran berhasil dicatat.';
        }

        $change = max(((int) ($data['amount_received'] ?? 0)) - $amount, 0);

        return $change > 0
            ? 'Pembayaran berhasil dicatat. Kembalian: ' . number_format($change, 0, ',', '.')
            : 'Pembayaran berhasil dicatat.';
    }
}
