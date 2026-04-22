<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteBillingProjectionFromWorkspaceRowsBuilder
{
    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function build(array $rows): array
    {
        $billing = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $outstanding = (int) ($row['outstanding_rupiah'] ?? 0);
            $netPaid = (int) ($row['net_paid_rupiah'] ?? 0);
            $refunded = (int) ($row['refunded_rupiah'] ?? 0);
            $hasStock = (bool) ($row['store_stock_count'] ?? 0);

            $billing[] = [
                'id' => (string) ($row['id'] ?? ''),
                'work_item_id' => (string) ($row['id'] ?? ''),
                'line_no' => (int) ($row['line_no'] ?? 0),
                'transaction_type' => (string) ($row['transaction_type'] ?? ''),
                'domain_type_label' => (string) ($row['type_label'] ?? 'Line Nota'),
                'component_type' => 'line_total',
                'component_ref_id' => (string) ($row['id'] ?? ''),
                'component_label' => (string) ($row['line_label'] ?? 'Total Line'),
                'component_group_label' => 'Line Total',
                'component_order' => 1,
                'component_total_rupiah' => (int) ($row['subtotal_rupiah'] ?? 0),
                'allocated_rupiah' => (int) ($row['allocated_rupiah'] ?? 0),
                'refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => $outstanding,
                'is_paid' => $outstanding <= 0,
                'is_outstanding' => $outstanding > 0,
                'is_product_component' => $hasStock,
                'is_service_component' => ! $hasStock,
                'eligible_for_dp_preset' => false,
                'can_select_manually' => $outstanding > 0,
                'selection_blocked_reason' => null,
                'status_label' => $outstanding <= 0 ? 'Lunas' : ($netPaid > 0 ? 'Parsial' : 'Belum Dibayar'),
            ];
        }

        return $billing;
    }
}
