<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Shared\DTO\Result;
use App\Core\Note\Note\Note;

final class UpdateTransactionWorkspaceResultBuilder
{
    public function success(Note $note): Result
    {
        return Result::success(
            [
                'note' => [
                    'id' => $note->id(),
                    'customer_name' => $note->customerName(),
                    'transaction_date' => $note->transactionDate()->format('Y-m-d'),
                    'total_rupiah' => $note->totalRupiah()->amount(),
                ],
            ],
            'Perubahan workspace nota berhasil disimpan.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function auditPayload(Note $note, int $itemsCount): array
    {
        return [
            'note_id' => $note->id(),
            'customer_name' => $note->customerName(),
            'transaction_date' => $note->transactionDate()->format('Y-m-d'),
            'items_count' => $itemsCount,
            'total_rupiah' => $note->totalRupiah()->amount(),
        ];
    }
}
