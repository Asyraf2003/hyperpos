<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\TransactionSummaryPerNoteRow;

final class TransactionSummaryPerNoteBuilder
{
    /**
     * @param list<array{
     *   note_id:string,
     *   transaction_date:string,
     *   customer_name:string,
     *   gross_transaction_rupiah:int,
     *   allocated_payment_rupiah:int,
     *   refunded_rupiah:int
     * }> $rows
     * @return list<TransactionSummaryPerNoteRow>
     */
    public function build(array $rows): array
    {
        return array_map(
            static fn (array $row): TransactionSummaryPerNoteRow => new TransactionSummaryPerNoteRow(
                $row['note_id'],
                $row['transaction_date'],
                $row['customer_name'],
                $row['gross_transaction_rupiah'],
                $row['allocated_payment_rupiah'],
                $row['refunded_rupiah'],
            ),
            $rows,
        );
    }
}
