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

        $allowedActions = array_values(array_map(
            'strval',
            is_array($summary['allowed_actions'] ?? null) ? $summary['allowed_actions'] : [],
        ));

        $lockReasons = array_values(array_map(
            'strval',
            is_array($summary['lock_reasons'] ?? null) ? $summary['lock_reasons'] : [],
        ));

        return [
            'badge_class' => $policyState === 'locked' ? 'bg-danger' : 'bg-success',
            'label' => $policyState === 'locked' ? 'Locked' : 'Editable',
            'allowed_actions' => array_map(
                fn (string $action): string => match ($action) {
                    'edit' => 'Edit invoice',
                    'void' => 'Void invoice',
                    'correction' => 'Correction / reversal',
                    default => $action,
                },
                $allowedActions,
            ),
            'lock_reasons' => array_map(
                fn (string $reason): string => match ($reason) {
                    'receipt_recorded' => 'Receipt sudah tercatat',
                    'payment_effective_recorded' => 'Payment efektif sudah tercatat',
                    default => $reason,
                },
                $lockReasons,
            ),
        ];
    }
}
