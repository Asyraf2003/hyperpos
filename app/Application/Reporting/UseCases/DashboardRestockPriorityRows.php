<?php

declare(strict_types=1);

namespace App\Application\Reporting\UseCases;

final class DashboardRestockPriorityRows
{
    /**
     * @param array<int, array<string, mixed>> $snapshotRows
     * @return array<int, array<string, int|string|null>>
     */
    public static function fromSnapshotRows(array $snapshotRows, int $limit = 5): array
    {
        $rows = [];

        foreach ($snapshotRows as $row) {
            $status = self::classify($row);

            if (! in_array($status, ['critical', 'low'], true)) {
                continue;
            }

            $rows[] = [
                'product_id' => (string) ($row['product_id'] ?? ''),
                'kode_barang' => $row['kode_barang'] ?? null,
                'nama_barang' => (string) ($row['nama_barang'] ?? '-'),
                'current_qty_on_hand' => (int) ($row['current_qty_on_hand'] ?? 0),
                'reorder_point_qty' => isset($row['reorder_point_qty']) ? (int) $row['reorder_point_qty'] : null,
                'critical_threshold_qty' => isset($row['critical_threshold_qty']) ? (int) $row['critical_threshold_qty'] : null,
                'status' => $status,
                'status_label' => $status === 'critical' ? 'Kritis' : 'Mulai Perlu Restok',
                'status_rank' => $status === 'critical' ? 0 : 1,
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            $severityCompare = ($left['status_rank'] ?? 99) <=> ($right['status_rank'] ?? 99);

            if ($severityCompare !== 0) {
                return $severityCompare;
            }

            $qtyCompare = ($left['current_qty_on_hand'] ?? 0) <=> ($right['current_qty_on_hand'] ?? 0);

            if ($qtyCompare !== 0) {
                return $qtyCompare;
            }

            return strcmp((string) ($left['product_id'] ?? ''), (string) ($right['product_id'] ?? ''));
        });

        return array_slice(array_map(static fn (array $row): array => [
            'product_id' => $row['product_id'],
            'kode_barang' => $row['kode_barang'],
            'nama_barang' => $row['nama_barang'],
            'current_qty_on_hand' => $row['current_qty_on_hand'],
            'reorder_point_qty' => $row['reorder_point_qty'],
            'critical_threshold_qty' => $row['critical_threshold_qty'],
            'status' => $row['status'],
            'status_label' => $row['status_label'],
        ], $rows), 0, $limit);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function classify(array $row): string
    {
        $reorderPointQty = $row['reorder_point_qty'] ?? null;
        $criticalThresholdQty = $row['critical_threshold_qty'] ?? null;

        if ($reorderPointQty === null || $criticalThresholdQty === null) {
            return 'unconfigured';
        }

        $qtyOnHand = (int) ($row['current_qty_on_hand'] ?? 0);

        if ($qtyOnHand <= (int) $criticalThresholdQty) {
            return 'critical';
        }

        if ($qtyOnHand <= (int) $reorderPointQty) {
            return 'low';
        }

        return 'safe';
    }
}
