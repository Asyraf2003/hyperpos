<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;

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

            $componentRows = $this->componentRows($row);

            if ($componentRows !== []) {
                array_push($billing, ...$componentRows);
                continue;
            }

            $billing[] = $this->lineTotalRow($row);
        }

        return $billing;
    }

    /**
     * @param array<string, mixed> $row
     * @return list<array<string, mixed>>
     */
    private function componentRows(array $row): array
    {
        $components = is_array($row['billing_components'] ?? null)
            ? array_values(array_filter($row['billing_components'], 'is_array'))
            : [];

        if ($components === []) {
            return [];
        }

        usort(
            $components,
            static fn (array $a, array $b): int => (int) ($a['component_order'] ?? 0) <=> (int) ($b['component_order'] ?? 0),
        );

        $billing = [];
        $allocatedRemainder = (int) ($row['allocated_rupiah'] ?? 0);
        $refundedRemainder = (int) ($row['refunded_rupiah'] ?? 0);
        $priorOutstanding = 0;
        $workItemId = (string) ($row['id'] ?? '');

        foreach ($components as $component) {
            $componentType = (string) ($component['component_type'] ?? '');
            $componentRefId = (string) ($component['component_ref_id'] ?? '');
            $total = (int) ($component['component_total_rupiah'] ?? 0);

            if ($componentType === '' || $componentRefId === '' || $total <= 0) {
                continue;
            }

            $allocated = min(max($allocatedRemainder, 0), $total);
            $allocatedRemainder -= $allocated;

            $refunded = min(max($refundedRemainder, 0), $allocated);
            $refundedRemainder -= $refunded;

            $netPaid = max($allocated - $refunded, 0);
            $outstanding = max($total - $netPaid, 0);
            $blocked = $priorOutstanding > 0 && $outstanding > 0;
            $priorOutstanding += $outstanding;

            $billing[] = [
                'id' => sprintf('%s::%s::%s', $workItemId, $componentType, $componentRefId),
                'work_item_id' => $workItemId,
                'line_no' => (int) ($row['line_no'] ?? 0),
                'transaction_type' => (string) ($row['transaction_type'] ?? ''),
                'domain_type_label' => (string) ($row['type_label'] ?? 'Line Nota'),
                'component_type' => $componentType,
                'component_ref_id' => $componentRefId,
                'component_label' => $this->componentLabel($componentType),
                'component_group_label' => $this->componentGroupLabel($componentType),
                'component_order' => (int) ($component['component_order'] ?? 1),
                'component_total_rupiah' => $total,
                'allocated_rupiah' => $allocated,
                'refunded_rupiah' => $refunded,
                'net_paid_rupiah' => $netPaid,
                'outstanding_rupiah' => $outstanding,
                'is_paid' => $outstanding <= 0,
                'is_outstanding' => $outstanding > 0,
                'is_product_component' => $this->isProductComponent($componentType),
                'is_service_component' => ! $this->isProductComponent($componentType),
                'eligible_for_dp_preset' => ! $this->isProductComponent($componentType) && $outstanding > 0,
                'can_select_manually' => ! $blocked && $outstanding > 0,
                'selection_blocked_reason' => $blocked ? 'Komponen sebelumnya pada line ini belum lunas. Ikuti urutan tagihan existing.' : null,
                'status_label' => $outstanding <= 0 ? 'Lunas' : ($netPaid > 0 ? 'Parsial' : 'Belum Dibayar'),
            ];
        }

        return $billing;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function lineTotalRow(array $row): array
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

    private function isProductComponent(string $type): bool
    {
        return in_array($type, [
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
            PaymentComponentType::SERVICE_STORE_STOCK_PART,
            PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART,
        ], true);
    }

    private function componentLabel(string $type): string
    {
        return match ($type) {
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM => 'Produk Toko',
            PaymentComponentType::SERVICE_STORE_STOCK_PART => 'Part Toko',
            PaymentComponentType::SERVICE_EXTERNAL_PURCHASE_PART => 'Part External',
            PaymentComponentType::SERVICE_FEE => 'Jasa',
            default => 'Komponen Tagihan',
        };
    }

    private function componentGroupLabel(string $type): string
    {
        return $this->isProductComponent($type) ? 'Produk' : 'Jasa';
    }
}
