<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

trait ProcurementInvoicePolicySqlFragments
{
    private function policyStateSelect(): Expression
    {
        return DB::raw("
            CASE
                WHEN supplier_invoices.voided_at IS NOT NULL
                THEN 'voided'
                WHEN COALESCE(receipt_counts.receipt_count, 0) > 0
                  OR COALESCE(payment_totals.total_paid_rupiah, 0) > 0
                THEN 'locked'
                ELSE 'editable'
            END as policy_state
        ");
    }

    private function allowedActionsSelect(): Expression
    {
        return DB::raw("
            CASE
                WHEN supplier_invoices.voided_at IS NOT NULL
                THEN ''
                WHEN COALESCE(receipt_counts.receipt_count, 0) > 0
                  OR COALESCE(payment_totals.total_paid_rupiah, 0) > 0
                THEN 'correction'
                ELSE 'edit,void'
            END as allowed_actions_csv
        ");
    }

    private function lockReasonsSelect(): Expression
    {
        return DB::raw("
            CASE
                WHEN supplier_invoices.voided_at IS NOT NULL
                THEN 'voided'
                ELSE TRIM(BOTH ',' FROM CONCAT(
                    CASE
                        WHEN COALESCE(receipt_counts.receipt_count, 0) > 0
                        THEN 'receipt_recorded'
                        ELSE ''
                    END,
                    CASE
                        WHEN COALESCE(receipt_counts.receipt_count, 0) > 0
                         AND COALESCE(payment_totals.total_paid_rupiah, 0) > 0
                        THEN ','
                        ELSE ''
                    END,
                    CASE
                        WHEN COALESCE(payment_totals.total_paid_rupiah, 0) > 0
                        THEN 'payment_effective_recorded'
                        ELSE ''
                    END
                ))
            END as lock_reasons_csv
        ");
    }
}
