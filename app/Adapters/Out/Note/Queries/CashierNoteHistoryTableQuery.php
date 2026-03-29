<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use App\Application\Note\Services\NotePaymentStatusResolver;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class CashierNoteHistoryTableQuery
{
    public function __construct(
        private readonly NotePaymentStatusResolver $paymentStatuses,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     * filters: array<string, mixed>,
     * items: list<array<string, mixed>>,
     * pagination: array<string, int>,
     * summary: array{label: string}
     * }
     */
    public function get(array $filters): array
    {
        $anchorDate = $this->resolveAnchorDate($filters['date'] ?? null);
        $previousDate = $anchorDate->modify('-1 day');

        $anchorDateText = $anchorDate->format('Y-m-d');
        $previousDateText = $previousDate->format('Y-m-d');

        $search = $this->normalizeString($filters['search'] ?? null);
        $paymentStatusFilter = $this->normalizeString($filters['payment_status'] ?? null);
        $workStatusFilter = $this->normalizeString($filters['work_status'] ?? null);

        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = 10;

        $rows = $this->baseQuery($anchorDateText, $previousDateText, $search)
            ->orderByDesc('notes.transaction_date')
            ->orderByDesc('notes.id')
            ->get();

        $items = [];

        foreach ($rows as $row) {
            $grandTotal = (int) $row->total_rupiah;
            $allocated = (int) ($row->allocated_rupiah ?? 0);
            $refunded = (int) ($row->refunded_rupiah ?? 0);
            $netPaid = max($allocated - $refunded, 0);
            $outstanding = max($grandTotal - $netPaid, 0);
            $paymentStatus = $this->paymentStatuses->resolve($grandTotal, $netPaid);

            $transactionDate = (string) $row->transaction_date;

            $isAnchorDate = $transactionDate === $anchorDateText;
            $isPreviousCarryOver = $transactionDate === $previousDateText && $paymentStatus !== 'paid';

            if (! $isAnchorDate && ! $isPreviousCarryOver) {
                continue;
            }

            $openCount = (int) ($row->open_count ?? 0);
            $doneCount = (int) ($row->done_count ?? 0);
            $canceledCount = (int) ($row->canceled_count ?? 0);

            if ($paymentStatusFilter !== '' && $paymentStatus !== $paymentStatusFilter) {
                continue;
            }

            if ($workStatusFilter !== '' && ! $this->matchesWorkStatusFilter(
                $workStatusFilter,
                $openCount,
                $doneCount,
                $canceledCount,
            )) {
                continue;
            }

            $items[] = [
                'note_id' => (string) $row->id,
                'transaction_date' => $transactionDate,
                'note_number' => (string) $row->id,
                'customer_name' => $this->buildCustomerLabel(
                    (string) $row->customer_name,
                    $row->customer_phone !== null ? (string) $row->customer_phone : null,
                ),
                'grand_total_text' => $this->formatRupiah($grandTotal),
                'total_paid_text' => $this->formatRupiah($netPaid),
                'outstanding_text' => $this->formatRupiah($outstanding),
                'payment_status_label' => $this->mapPaymentStatusLabel($paymentStatus),
                'work_status_label' => $this->buildWorkSummary($openCount, $doneCount, $canceledCount),
                'action_label' => 'Buka Detail',
                'action_url' => route('cashier.notes.show', ['noteId' => (string) $row->id]),
            ];
        }

        $total = count($items);
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $pagedItems = array_values(array_slice($items, $offset, $perPage));

        return [
            'filters' => [
                'date' => $anchorDateText,
                'search' => $search,
                'payment_status' => $paymentStatusFilter,
                'work_status' => $workStatusFilter,
            ],
            'items' => $pagedItems,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'summary' => [
                'label' => sprintf(
                    'Window kasir %s dan %s. Nota kemarin hanya ditampilkan bila masih open.',
                    $previousDateText,
                    $anchorDateText,
                ),
            ],
        ];
    }

    private function baseQuery(string $anchorDate, string $previousDate, string $search)
    {
        $allocationTotals = DB::table('payment_allocations')
            ->selectRaw('note_id, COALESCE(SUM(amount_rupiah), 0) as allocated_rupiah')
            ->groupBy('note_id');

        $refundTotals = DB::table('customer_refunds')
            ->selectRaw('note_id, COALESCE(SUM(amount_rupiah), 0) as refunded_rupiah')
            ->groupBy('note_id');

        $workSummary = DB::table('work_items')
            ->selectRaw("
                note_id,
                COALESCE(SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END), 0) as open_count,
                COALESCE(SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END), 0) as done_count,
                COALESCE(SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END), 0) as canceled_count
            ")
            ->groupBy('note_id');

        return DB::table('notes')
            ->leftJoinSub($allocationTotals, 'allocation_totals', fn ($join) => $join->on('allocation_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($refundTotals, 'refund_totals', fn ($join) => $join->on('refund_totals.note_id', '=', 'notes.id'))
            ->leftJoinSub($workSummary, 'work_summary', fn ($join) => $join->on('work_summary.note_id', '=', 'notes.id'))
            ->whereBetween('notes.transaction_date', [$previousDate, $anchorDate])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('notes.id', 'like', '%' . $search . '%')
                        ->orWhere('notes.customer_name', 'like', '%' . $search . '%')
                        ->orWhere('notes.customer_phone', 'like', '%' . $search . '%');
                });
            })
            ->select([
                'notes.id',
                'notes.customer_name',
                'notes.customer_phone',
                'notes.transaction_date',
                'notes.total_rupiah',
                DB::raw('COALESCE(allocation_totals.allocated_rupiah, 0) as allocated_rupiah'),
                DB::raw('COALESCE(refund_totals.refunded_rupiah, 0) as refunded_rupiah'),
                DB::raw('COALESCE(work_summary.open_count, 0) as open_count'),
                DB::raw('COALESCE(work_summary.done_count, 0) as done_count'),
                DB::raw('COALESCE(work_summary.canceled_count, 0) as canceled_count'),
            ]);
    }

    private function resolveAnchorDate(mixed $value): DateTimeImmutable
    {
        if (! is_string($value)) {
            return new DateTimeImmutable(date('Y-m-d'));
        }

        $trimmed = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $trimmed);

        if ($parsed === false || $parsed->format('Y-m-d') !== $trimmed) {
            return new DateTimeImmutable(date('Y-m-d'));
        }

        return $parsed;
    }

    private function normalizeString(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function matchesWorkStatusFilter(
        string $workStatus,
        int $openCount,
        int $doneCount,
        int $canceledCount,
    ): bool {
        return match ($workStatus) {
            'open' => $openCount > 0,
            'done' => $doneCount > 0,
            'canceled' => $canceledCount > 0,
            default => true,
        };
    }

    private function buildCustomerLabel(string $customerName, ?string $customerPhone): string
    {
        $name = trim($customerName);
        $phone = $customerPhone !== null ? trim($customerPhone) : '';

        if ($phone === '') {
            return $name;
        }

        return $name . ' / ' . $phone;
    }

    private function buildWorkSummary(int $openCount, int $doneCount, int $canceledCount): string
    {
        return sprintf(
            'Open: %d • Selesai: %d • Batal: %d',
            $openCount,
            $doneCount,
            $canceledCount,
        );
    }

    private function mapPaymentStatusLabel(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'paid' => 'Lunas',
            'partial' => 'Dibayar Sebagian',
            default => 'Belum Dibayar',
        };
    }

    private function formatRupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
