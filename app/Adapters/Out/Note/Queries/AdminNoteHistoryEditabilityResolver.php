<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

final class AdminNoteHistoryEditabilityResolver
{
    public function key(string $noteState, string $paymentStatus): string
    {
        if ($noteState === 'closed') {
            return 'admin_strict';
        }

        if ($paymentStatus === 'paid') {
            return 'correction_only';
        }

        return 'editable_normal';
    }

    public function label(string $key): string
    {
        return match ($key) {
            'admin_strict' => 'Admin Ketat',
            'correction_only' => 'Correction Only',
            default => 'Editable Normal',
        };
    }
}
