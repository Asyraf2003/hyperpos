<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NoteDetailNotePayloadBuilder
{
    public function build(
        array $base,
        array $workspacePanel,
        array $operational,
        array $refundPaymentOptions,
        array $refundRows,
        array $billingRows,
        array $revisionTimeline,
        array $history,
        bool $isOpen,
        bool $isClosed,
        bool $isRefunded,
        bool $hasOutstandingBillingRow,
    ): array {
        $openLineCount = (int) ($workspacePanel['line_summary']['open_count'] ?? 0);
        $closeLineCount = (int) ($workspacePanel['line_summary']['close_count'] ?? 0);
        $refundRequired = max((int) $operational['net_paid_rupiah'] - (int) $operational['grand_total_rupiah'], 0);

        return $base + [
            'operational_status' => $operational['operational_status'],
            'is_open' => $isOpen,
            'is_closed' => $isClosed,
            'is_refunded' => $isRefunded,
            'grand_total_rupiah' => $operational['grand_total_rupiah'],
            'total_allocated_rupiah' => $operational['total_allocated_rupiah'],
            'total_refunded_rupiah' => $operational['total_refunded_rupiah'],
            'net_paid_rupiah' => $operational['net_paid_rupiah'],
            'outstanding_rupiah' => $operational['outstanding_rupiah'],
            'refund_required_rupiah' => $refundRequired,
            'can_add_rows' => $isOpen,
            'can_show_edit_actions' => $isOpen,
            'can_edit_workspace' => $isOpen,
            'can_show_workspace_panel' => $isOpen || $isClosed,
            'can_show_payment_form' => $isOpen && $openLineCount > 0 && $hasOutstandingBillingRow,
            'can_show_refund_form' => $closeLineCount > 0 && $refundPaymentOptions !== [] && $refundRows !== [],
            'refund_payment_options' => $refundPaymentOptions,
            'can_show_correction_actions' => false,
            'correction_notice' => $isClosed
                ? 'Nota sudah close. Pembalikan dilakukan lewat refund flow.'
                : ($isRefunded ? 'Nota sudah refunded. Workspace tidak dipakai lagi.' : null),
            'line_summary' => $workspacePanel['line_summary'],
            'rows' => $workspacePanel['rows'],
            'refund_rows' => $refundRows,
            'billing_rows' => $billingRows,
            'revision_timeline' => $revisionTimeline,
            'pseudo_versioning' => $revisionTimeline,
            'correction_history' => $history,
        ];
    }
}
