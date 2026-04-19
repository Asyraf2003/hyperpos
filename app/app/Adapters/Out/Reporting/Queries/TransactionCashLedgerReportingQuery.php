<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries;

final class TransactionCashLedgerReportingQuery
{
    public function __construct(
        private readonly TransactionCashLedgerPaymentRowsQuery $paymentRows,
        private readonly TransactionCashLedgerRefundRowsQuery $refundRows,
    ) {
    }

    public function rows(string $fromEventDate, string $toEventDate): array
    {
        return $this->paymentRows->rows($fromEventDate, $toEventDate)
            ->concat($this->refundRows->rows($fromEventDate, $toEventDate))
            ->sortBy([['event_date', 'asc'], ['event_type', 'asc'], ['note_id', 'asc']])
            ->values()
            ->all();
    }

    public function reconciliation(string $fromEventDate, string $toEventDate): array
    {
        $rows = $this->rows($fromEventDate, $toEventDate);

        return [
            'total_in_rupiah' => array_sum(array_column(
                array_filter($rows, static fn (array $row): bool => $row['direction'] === 'in'),
                'event_amount_rupiah'
            )),
            'total_out_rupiah' => array_sum(array_column(
                array_filter($rows, static fn (array $row): bool => $row['direction'] === 'out'),
                'event_amount_rupiah'
            )),
        ];
    }
}
