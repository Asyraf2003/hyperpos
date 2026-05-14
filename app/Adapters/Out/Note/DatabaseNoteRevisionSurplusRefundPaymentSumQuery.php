<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Application\Note\DTO\NoteRevisionSurplusRefundPayment;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteRevisionSurplusRefundPaymentSumQuery
{
    public function sumActiveAmountByDispositionId(string $dispositionId): int
    {
        return $this->sumActiveAmountBy(
            'note_revision_surplus_disposition_id',
            $dispositionId,
        );
    }

    public function sumActiveAmountByNoteRootId(string $noteRootId): int
    {
        return $this->sumActiveAmountBy('note_root_id', $noteRootId);
    }

    private function sumActiveAmountBy(string $column, string $value): int
    {
        $normalized = trim($value);

        if ($normalized === '') {
            return 0;
        }

        return (int) DB::table('note_revision_surplus_refund_payments')
            ->where($column, $normalized)
            ->where('status', NoteRevisionSurplusRefundPayment::STATUS_ACTIVE)
            ->sum('amount_rupiah');
    }
}
