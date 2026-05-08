<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteBillingProjectionComponentRowFactory
{
    public function __construct(
        private readonly NoteBillingProjectionComponentClassifier $classifier,
    ) {
    }

    /** @param array<string, mixed> $row */
    public function build(
        array $row,
        array $component,
        string $type,
        string $refId,
        int $total,
        int $allocated,
        int $refunded,
        int $netPaid,
        int $outstanding,
        bool $blocked,
    ): array {
        $workItemId = (string) ($row['id'] ?? '');

        return [
            'id' => sprintf('%s::%s::%s', $workItemId, $type, $refId),
            'work_item_id' => $workItemId,
            'line_no' => (int) ($row['line_no'] ?? 0),
            'transaction_type' => (string) ($row['transaction_type'] ?? ''),
            'domain_type_label' => (string) ($row['type_label'] ?? 'Line Nota'),
            'component_type' => $type,
            'component_ref_id' => $refId,
            'component_label' => $this->classifier->label($type),
            'component_group_label' => $this->classifier->groupLabel($type),
            'component_order' => (int) ($component['component_order'] ?? 1),
            'component_total_rupiah' => $total,
            'allocated_rupiah' => $allocated,
            'refunded_rupiah' => $refunded,
            'net_paid_rupiah' => $netPaid,
            'outstanding_rupiah' => $outstanding,
            'is_paid' => $outstanding <= 0,
            'is_outstanding' => $outstanding > 0,
            'is_product_component' => $this->classifier->isProduct($type),
            'is_service_component' => ! $this->classifier->isProduct($type),
            'eligible_for_dp_preset' => ! $this->classifier->isProduct($type) && $outstanding > 0,
            'can_select_manually' => ! $blocked && $outstanding > 0,
            'selection_blocked_reason' => $blocked ? 'Komponen sebelumnya pada line ini belum lunas. Ikuti urutan tagihan existing.' : null,
            'status_label' => $outstanding <= 0 ? 'Lunas' : ($netPaid > 0 ? 'Parsial' : 'Belum Dibayar'),
        ];
    }
}
