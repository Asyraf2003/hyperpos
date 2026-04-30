<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Support\Carbon;
use Throwable;

final class CashierNoteHistoryValueFormatter
{
    public function customerLabel(string $name, ?string $phone): string
    {
        $phoneText = $phone !== null ? trim($phone) : '';

        if ($phoneText === '') {
            return trim($name);
        }

        return trim($name) . ' / ' . $phoneText;
    }

    public function workSummary(int $openCount, int $doneCount, int $canceledCount): string
    {
        return sprintf(
            'Open: %d • Selesai: %d • Batal: %d',
            $openCount,
            $doneCount,
            $canceledCount,
        );
    }

    public function lineSummary(int $openCount, int $closeCount, int $refundCount): string
    {
        $parts = [];

        if ($openCount > 0) {
            $parts[] = sprintf('%d Open', $openCount);
        }

        if ($closeCount > 0) {
            $parts[] = sprintf('%d Close', $closeCount);
        }

        if ($refundCount > 0) {
            $parts[] = sprintf('%d Refund', $refundCount);
        }

        return $parts === [] ? 'Belum ada line.' : implode(', ', $parts);
    }

    public function paymentStatusLabel(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'paid' => 'Lunas',
            'partial' => 'Dibayar Sebagian',
            default => 'Belum Dibayar',
        };
    }


    public function date(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $text = (string) $value;

        if (preg_match('/^\\d{2}\\/\\d{2}\\/\\d{4}/', $text) === 1) {
            return $text;
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (Throwable) {
            return $text;
        }
    }

    public function rupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
