<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteBillingProjectionLineTotalRowBuilder
{
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function build(array $row): array
    {
        $outstanding = (int) ($row['outstanding_rupiah'] ?? 0);
        $netPaid = (int) ($row['net_paid_rupiah'] ?? 0);
        $refunded = (int) ($row['refunded_rupiah'] ?? 0);
        $hasStock = (bool) ($row['store_stock_count'] ?? 0);

        return [
            'id' => (string) ($row['id'] ?? ''),
            'work_item_id' => (string) ($row['id'] ?? ''),
            'line_no' => (int) ($row['line_no'] ?? 0),
            'transaction_type' => (string) ($row['transaction_type'] ?? ''),
            'domain_type_label' => (string) ($row['type_label'] ?? 'Rincian Nota'),
            'component_type' => 'line_total',
            'component_ref_id' => (string) ($row['id'] ?? ''),
            'component_label' => (string) ($row['line_label'] ?? 'Total Rincian'),
            'component_group_label' => 'Total Rincian',
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
}
