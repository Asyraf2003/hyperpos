<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns;

trait BuildsProcurementInvoiceDetailPolicyView
{
    /**
     * @param array<string, mixed> $summary
     * @return array<string, mixed>
     */
    private function buildPolicyView(array $summary): array
    {
        $policyState = (string) ($summary['policy_state'] ?? 'editable');
        $supplierInvoiceId = (string) ($summary['supplier_invoice_id'] ?? '');

        $allowedActions = array_values(array_map(
            'strval',
            is_array($summary['allowed_actions'] ?? null) ? $summary['allowed_actions'] : [],
        ));

        $lockReasons = array_values(array_map(
            'strval',
            is_array($summary['lock_reasons'] ?? null) ? $summary['lock_reasons'] : [],
        ));

        $allowedActionLabels = array_map(
            fn (string $action): string => match ($action) {
                'edit' => 'Edit nota',
                'void' => 'Hapus nota',
                'correction' => 'Koreksi',
                default => $action,
            },
            $allowedActions,
        );

        $primaryAction = null;

        if ($supplierInvoiceId !== '' && $policyState !== 'voided') {
            if (in_array('correction', $allowedActions, true)) {
                $primaryAction = [
                    'label' => 'Koreksi',
                    'url' => route('admin.procurement.supplier-invoices.revise', [
                        'supplierInvoiceId' => $supplierInvoiceId,
                    ]),
                    'button_class' => 'btn btn-warning',
                ];
            } elseif (in_array('edit', $allowedActions, true)) {
                $primaryAction = [
                    'label' => 'Edit nota',
                    'url' => route('admin.procurement.supplier-invoices.edit', [
                        'supplierInvoiceId' => $supplierInvoiceId,
                    ]),
                    'button_class' => 'btn btn-primary',
                ];
            }
        }

        return [
            'state' => $policyState,
            'is_voided' => $policyState === 'voided',
            'badge_class' => match ($policyState) {
                'voided' => 'bg-secondary',
                'locked' => 'bg-danger',
                default => 'bg-success',
            },
            'label' => match ($policyState) {
                'voided' => 'Dibatalkan',
                'locked' => 'Locked',
                default => 'Editable',
            },
            'allowed_actions' => $allowedActionLabels,
            'lock_reasons' => array_map(
                fn (string $reason): string => match ($reason) {
                    'receipt_recorded' => 'Receipt sudah tercatat',
                    'payment_effective_recorded' => 'Payment efektif sudah tercatat',
                    'voided' => 'Nota sudah dibatalkan',
                    default => $reason,
                },
                $lockReasons,
            ),
            'primary_action' => $primaryAction,
        ];
    }
}
