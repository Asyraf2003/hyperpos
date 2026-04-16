<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class SupplierPayableDueStatusResolver
{
    public function resolve(
        string $dueDate,
        int $outstandingRupiah,
        string $referenceDate,
    ): array {
        if ($outstandingRupiah <= 0) {
            return [
                'due_status' => 'settled',
                'due_status_label' => 'Lunas',
            ];
        }

        if ($dueDate > $referenceDate) {
            return [
                'due_status' => 'not_due',
                'due_status_label' => 'Belum Jatuh Tempo',
            ];
        }

        if ($dueDate === $referenceDate) {
            return [
                'due_status' => 'due_today',
                'due_status_label' => 'Jatuh Tempo Hari Ini',
            ];
        }

        return [
            'due_status' => 'overdue',
            'due_status_label' => 'Lewat Jatuh Tempo',
        ];
    }
}
