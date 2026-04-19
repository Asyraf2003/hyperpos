<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteLineSummaryBuilder
{
    /**
     * @param list<array<string, mixed>> $rows
     * @return array{
     *   open_count: int,
     *   close_count: int,
     *   refund_count: int,
     *   summary_label: string
     * }
     */
    public function build(array $rows): array
    {
        $openCount = 0;
        $closeCount = 0;
        $refundCount = 0;

        foreach ($rows as $row) {
            $status = (string) ($row['line_status'] ?? '');

            if ($status === WorkItemOperationalStatusResolver::STATUS_OPEN) {
                $openCount++;
                continue;
            }

            if ($status === WorkItemOperationalStatusResolver::STATUS_CLOSE) {
                $closeCount++;
                continue;
            }

            if ($status === WorkItemOperationalStatusResolver::STATUS_REFUND) {
                $refundCount++;
            }
        }

        return [
            'open_count' => $openCount,
            'close_count' => $closeCount,
            'refund_count' => $refundCount,
            'summary_label' => $this->label($openCount, $closeCount, $refundCount),
        ];
    }

    private function label(int $openCount, int $closeCount, int $refundCount): string
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
}
