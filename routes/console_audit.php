<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$seedAuditCount = function (string $table, ?callable $filter = null): int {
    if (! Schema::hasTable($table)) {
        return 0;
    }

    $query = DB::table($table);

    if ($filter !== null) {
        $filter($query);
    }

    return (int) $query->count();
};

$seedAuditSum = function (string $table, string $column, ?callable $filter = null): int {
    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
        return 0;
    }

    $query = DB::table($table);

    if ($filter !== null) {
        $filter($query);
    }

    return (int) $query->sum($column);
};

$seedAuditTableExists = static fn (string $table): bool => Schema::hasTable($table);

$seedAuditPrintLine = static function (string $label, int|string $value): void {
    echo $label . ': ' . $value . PHP_EOL;
};

Artisan::command('audit:seed-level {level}', function () use (
    $seedAuditCount,
    $seedAuditPrintLine,
    $seedAuditTableExists
): int {
    $level = (string) $this->argument('level');

    if (! in_array($level, ['1', '2', '3'], true)) {
        $this->error('Level harus salah satu dari: 1, 2, 3.');

        return 1;
    }

    $failures = 0;

    $this->line('== SEED LEVEL AUDIT ==');
    $seedAuditPrintLine('level', $level);

    if ($level === '1') {
        $usersTotal = $seedAuditCount('users');
        $adminUsers = $seedAuditCount('users', static fn ($query) => $query->where('email', 'admin@example.com'));
        $kasirUsers = $seedAuditCount('users', static fn ($query) => $query->where('email', 'kasir@example.com'));

        $actorAccessesTotal = $seedAuditCount('actor_accesses');
        $cashierAreaAccess = $seedAuditCount('admin_cashier_area_access_states');
        $transactionCapability = $seedAuditCount('admin_transaction_capability_states');

        $seedAuditPrintLine('users total', $usersTotal);
        $seedAuditPrintLine('admin user count', $adminUsers);
        $seedAuditPrintLine('kasir user count', $kasirUsers);
        $seedAuditPrintLine('actor_accesses total', $actorAccessesTotal);
        $seedAuditPrintLine('admin cashier area active count', $cashierAreaAccess);
        $seedAuditPrintLine('admin transaction capability active count', $transactionCapability);

        foreach ([
            'users total' => $usersTotal === 2,
            'admin user count' => $adminUsers === 1,
            'kasir user count' => $kasirUsers === 1,
            'actor_accesses total' => $actorAccessesTotal === 2,
            'admin cashier area active count' => $cashierAreaAccess === 1,
            'admin transaction capability active count' => $transactionCapability === 1,
        ] as $check => $passed) {
            if (! $passed) {
                $this->error('FAILED: ' . $check);
                $failures++;
            }
        }

        $seedAuditPrintLine('failures', $failures);

        return $failures === 0 ? 0 : 1;
    }

    if ($level === '3') {
        $window = \Database\Seeders\Support\SeedWindow::loadYear();
        $density = \Database\Seeders\Support\SeedDensity::monster();

        $expectedInvoices = 0;
        $expectedReceipts = 0;
        $expectedPayments = 0;
        $expectedFullPayments = 0;
        $expectedPartialPayments = 0;
        $expectedPendingProofs = 0;
        $expectedUploadedProofs = 0;
        $expectedInventoryMovements = 0;
        $invoiceRunningNo = 1;

        foreach ($window['days'] as $dayIndex => $day) {
            $weekday = (int) $day->dayOfWeekIso;
            $invoiceCount = in_array($weekday, [2, 4, 6], true)
                ? (int) $density['procurement_invoices_spike_per_day']
                : (int) $density['procurement_invoices_normal_per_day'];

            if ((int) $day->day >= 26) {
                $invoiceCount = (int) ceil($invoiceCount * ((int) $density['month_end_procurement_multiplier_percent']) / 100);
            }

            $invoiceCount = max(1, $invoiceCount);
            $expectedInvoices += $invoiceCount;

            for ($slot = 1; $slot <= $invoiceCount; $slot++) {
                $statePattern = ($invoiceRunningNo - 1) % 6;
                $lineCount = 4 + (($invoiceRunningNo + $slot + $dayIndex) % 5);

                if (in_array($statePattern, [1, 2, 3, 4, 5], true)) {
                    $expectedReceipts++;
                    $expectedInventoryMovements += $lineCount;
                }

                if (in_array($statePattern, [2, 3, 4, 5], true)) {
                    $expectedPayments++;

                    if ($statePattern === 2) {
                        $expectedPartialPayments++;
                    } else {
                        $expectedFullPayments++;
                    }

                    if (in_array($statePattern, [4, 5], true)) {
                        $expectedUploadedProofs++;
                    } else {
                        $expectedPendingProofs++;
                    }
                }

                $invoiceRunningNo++;
            }
        }

        $expectedExpenses = count($window['days']) * (int) $density['expense_rows_per_day'];

        $this->line('');
        $this->line('== LEVEL 3 LOAD COUNTS ==');
        $seedAuditPrintLine('window start', $window['start']->format('Y-m-d'));
        $seedAuditPrintLine('window end', $window['end']->format('Y-m-d'));
        $seedAuditPrintLine('window days', count($window['days']));

        $loadInvoices = $seedAuditCount('supplier_invoices', static fn ($query) => $query->where('id', 'like', 'seed-load-si-%'));
        $loadReceipts = $seedAuditCount('supplier_receipts', static fn ($query) => $query->where('id', 'like', 'seed-load-sr-%'));
        $loadReceiptLines = $seedAuditCount('supplier_receipt_lines', static fn ($query) => $query->where('id', 'like', 'seed-load-sr-%'));
        $loadPayments = $seedAuditCount('supplier_payments', static fn ($query) => $query->where('id', 'like', 'seed-load-sp-%'));
        $loadInventoryMovements = $seedAuditCount('inventory_movements', static fn ($query) => $query->where('id', 'like', 'seed-load-im-%'));
        $loadAuditLogs = $seedAuditCount('audit_logs', static fn ($query) => $query
            ->where('event', 'supplier_receipt_created')
            ->where('context', 'like', '%seed-load-sr-%')
        );
        $loadExpenses = $seedAuditCount('operational_expenses', static fn ($query) => $query->where('id', 'like', 'seed-exp-load-%'));

        $loadFullPayments = $seedAuditCount('supplier_payments', static fn ($query) => $query
            ->join('supplier_invoices', 'supplier_invoices.id', '=', 'supplier_payments.supplier_invoice_id')
            ->where('supplier_payments.id', 'like', 'seed-load-sp-%')
            ->whereColumn('supplier_payments.amount_rupiah', '=', 'supplier_invoices.grand_total_rupiah')
        );
        $loadPartialPayments = $seedAuditCount('supplier_payments', static fn ($query) => $query
            ->join('supplier_invoices', 'supplier_invoices.id', '=', 'supplier_payments.supplier_invoice_id')
            ->where('supplier_payments.id', 'like', 'seed-load-sp-%')
            ->whereColumn('supplier_payments.amount_rupiah', '<', 'supplier_invoices.grand_total_rupiah')
        );
        $loadPendingProofs = $seedAuditCount('supplier_payments', static fn ($query) => $query
            ->where('id', 'like', 'seed-load-sp-%')
            ->where('proof_status', 'pending')
        );
        $loadUploadedProofs = $seedAuditCount('supplier_payments', static fn ($query) => $query
            ->where('id', 'like', 'seed-load-sp-%')
            ->where('proof_status', 'uploaded')
        );

        $seedAuditPrintLine('expected procurement invoices', $expectedInvoices);
        $seedAuditPrintLine('load procurement invoices', $loadInvoices);
        $seedAuditPrintLine('expected supplier receipts', $expectedReceipts);
        $seedAuditPrintLine('load supplier receipts', $loadReceipts);
        $seedAuditPrintLine('expected supplier receipt lines', $expectedInventoryMovements);
        $seedAuditPrintLine('load supplier receipt lines', $loadReceiptLines);
        $seedAuditPrintLine('expected supplier payments', $expectedPayments);
        $seedAuditPrintLine('load supplier payments', $loadPayments);
        $seedAuditPrintLine('expected supplier payments full', $expectedFullPayments);
        $seedAuditPrintLine('load supplier payments full', $loadFullPayments);
        $seedAuditPrintLine('expected supplier payments partial', $expectedPartialPayments);
        $seedAuditPrintLine('load supplier payments partial', $loadPartialPayments);
        $seedAuditPrintLine('expected supplier payments proof pending', $expectedPendingProofs);
        $seedAuditPrintLine('load supplier payments proof pending', $loadPendingProofs);
        $seedAuditPrintLine('expected supplier payments proof uploaded', $expectedUploadedProofs);
        $seedAuditPrintLine('load supplier payments proof uploaded', $loadUploadedProofs);
        $seedAuditPrintLine('expected inventory movements', $expectedInventoryMovements);
        $seedAuditPrintLine('load inventory movements', $loadInventoryMovements);
        $seedAuditPrintLine('load audit logs', $loadAuditLogs);
        $seedAuditPrintLine('expected operational expenses load', $expectedExpenses);
        $seedAuditPrintLine('load operational expenses', $loadExpenses);

        $orphanLoadInvoiceLines = $seedAuditTableExists('supplier_invoice_lines')
            ? DB::table('supplier_invoice_lines as lines')
                ->leftJoin('supplier_invoices as invoices', 'invoices.id', '=', 'lines.supplier_invoice_id')
                ->where('lines.supplier_invoice_id', 'like', 'seed-load-si-%')
                ->whereNull('invoices.id')
                ->count()
            : 0;

        $orphanLoadReceiptLines = $seedAuditTableExists('supplier_receipt_lines')
            ? DB::table('supplier_receipt_lines as lines')
                ->leftJoin('supplier_receipts as receipts', 'receipts.id', '=', 'lines.supplier_receipt_id')
                ->where('lines.id', 'like', 'seed-load-sr-%')
                ->whereNull('receipts.id')
                ->count()
            : 0;

        $seedAuditPrintLine('orphan load supplier invoice lines', (int) $orphanLoadInvoiceLines);
        $seedAuditPrintLine('orphan load supplier receipt lines', (int) $orphanLoadReceiptLines);

        foreach ([
            'load procurement invoices' => $loadInvoices === $expectedInvoices,
            'load supplier receipts' => $loadReceipts === $expectedReceipts,
            'load supplier receipt lines' => $loadReceiptLines === $expectedInventoryMovements,
            'load supplier payments' => $loadPayments === $expectedPayments,
            'load supplier payments full' => $loadFullPayments === $expectedFullPayments,
            'load supplier payments partial' => $loadPartialPayments === $expectedPartialPayments,
            'load supplier payments proof pending' => $loadPendingProofs === $expectedPendingProofs,
            'load supplier payments proof uploaded' => $loadUploadedProofs === $expectedUploadedProofs,
            'load inventory movements' => $loadInventoryMovements === $expectedInventoryMovements,
            'load audit logs' => $loadAuditLogs === $expectedReceipts,
            'load operational expenses' => $loadExpenses === $expectedExpenses,
            'orphan load supplier invoice lines' => (int) $orphanLoadInvoiceLines === 0,
            'orphan load supplier receipt lines' => (int) $orphanLoadReceiptLines === 0,
        ] as $check => $passed) {
            if (! $passed) {
                $this->error('FAILED: ' . $check);
                $failures++;
            }
        }

        $this->line('');
        $this->line('== RESULT ==');
        $seedAuditPrintLine('failures', $failures);

        return $failures === 0 ? 0 : 1;
    }

    $this->line('');
    $this->line('== LEVEL 2 CORE COUNTS ==');

    $usersTotal = $seedAuditCount('users');
    $productsTotal = $seedAuditCount('products');
    $activeProducts = $seedAuditCount('products', static fn ($query) => $query->whereNull('deleted_at'));
    $productsMissingThreshold = $seedAuditCount('products', static function ($query): void {
        $query
            ->whereNull('deleted_at')
            ->where(static function ($nested): void {
                $nested
                    ->whereNull('reorder_point_qty')
                    ->orWhereNull('critical_threshold_qty');
            });
    });
    $suppliersTotal = $seedAuditCount('suppliers');
    $employeesTotal = $seedAuditCount('employees');

    $seedAuditPrintLine('users total', $usersTotal);
    $seedAuditPrintLine('products total', $productsTotal);
    $seedAuditPrintLine('active products', $activeProducts);
    $seedAuditPrintLine('products missing threshold active', $productsMissingThreshold);
    $seedAuditPrintLine('suppliers total', $suppliersTotal);
    $seedAuditPrintLine('employees total', $employeesTotal);

    $this->line('');
    $this->line('== SUPPLIER INVOICE LEVEL 2 COUNTS ==');

    $scenarioNos = [
        'SI-EDIT-001',
        'SI-RECV-001',
        'SI-PAYP-001',
        'SI-PROOF-001',
        'SI-FULL-001',
    ];

    $siBlInvoiceIds = DB::table('supplier_invoices')
        ->select('id')
        ->where('nomor_faktur', 'like', 'SI-BL-%');

    $siBlInvoices = $seedAuditCount('supplier_invoices', static fn ($query) => $query->where('nomor_faktur', 'like', 'SI-BL-%'));
    $siBlVersions = $seedAuditCount('supplier_invoice_versions', static fn ($query) => $query
        ->whereIn('supplier_invoice_id', DB::table('supplier_invoices')->select('id')->where('nomor_faktur', 'like', 'SI-BL-%'))
    );
    $siBlProjections = $seedAuditCount('supplier_invoice_list_projection', static fn ($query) => $query
        ->whereIn('supplier_invoice_id', DB::table('supplier_invoices')->select('id')->where('nomor_faktur', 'like', 'SI-BL-%'))
    );
    $scenarioInvoicesActive = $seedAuditCount('supplier_invoices', static fn ($query) => $query
        ->whereIn('nomor_faktur', $scenarioNos)
        ->whereNull('voided_at')
    );
    $voidScenarioInvoicesTotal = $seedAuditCount('supplier_invoices', static fn ($query) => $query
        ->whereIn('nomor_faktur', ['SI-VOID-001', 'SI-VOID-REUSE-001'])
    );
    $void001Voided = $seedAuditCount('supplier_invoices', static fn ($query) => $query
        ->where('nomor_faktur', 'SI-VOID-001')
        ->whereNotNull('voided_at')
    );
    $voidReuseVoided = $seedAuditCount('supplier_invoices', static fn ($query) => $query
        ->where('nomor_faktur', 'SI-VOID-REUSE-001')
        ->whereNotNull('voided_at')
    );
    $voidReuseActive = $seedAuditCount('supplier_invoices', static fn ($query) => $query
        ->where('nomor_faktur', 'SI-VOID-REUSE-001')
        ->whereNull('voided_at')
    );

    $seedAuditPrintLine('SI-BL invoices', $siBlInvoices);
    $seedAuditPrintLine('SI-BL versions', $siBlVersions);
    $seedAuditPrintLine('SI-BL projections', $siBlProjections);
    $seedAuditPrintLine('scenario invoices active', $scenarioInvoicesActive);
    $seedAuditPrintLine('void scenario invoices total', $voidScenarioInvoicesTotal);
    $seedAuditPrintLine('SI-VOID-001 voided', $void001Voided);
    $seedAuditPrintLine('SI-VOID-REUSE-001 voided', $voidReuseVoided);
    $seedAuditPrintLine('SI-VOID-REUSE-001 active', $voidReuseActive);

    $this->line('');
    $this->line('== CUSTOMER BASELINE COUNTS ==');

    $baselineNotes = $seedAuditCount('notes', static fn ($query) => $query->where('id', 'like', 'seed-note-bl-%'));
    $baselinePayments = $seedAuditCount('customer_payments', static fn ($query) => $query->where('id', 'like', 'seed-pay-bl-%'));
    $baselinePaymentAllocations = $seedAuditCount('payment_allocations', static fn ($query) => $query->where('id', 'like', 'seed-pay-alloc-bl-%'));
    $baselineRefunds = $seedAuditCount('customer_refunds', static fn ($query) => $query->where('id', 'like', 'seed-ref-bl-%'));

    $seedAuditPrintLine('baseline notes', $baselineNotes);
    $seedAuditPrintLine('baseline customer payments', $baselinePayments);
    $seedAuditPrintLine('baseline payment allocations', $baselinePaymentAllocations);
    $seedAuditPrintLine('baseline refunds', $baselineRefunds);

    $this->line('');
    $this->line('== EXPENSE BASELINE COUNTS ==');

    $baselineExpenses = $seedAuditCount('operational_expenses', static fn ($query) => $query->where('id', 'like', 'seed-exp-bl-%'));
    $expenseCategories = $seedAuditCount('expense_categories');

    $seedAuditPrintLine('baseline expenses', $baselineExpenses);
    $seedAuditPrintLine('expense categories', $expenseCategories);

    $this->line('');
    $this->line('== ORPHAN / DUPLICATE CHECKS ==');

    $orphanSupplierInvoiceLines = $seedAuditTableExists('supplier_invoice_lines')
        ? DB::table('supplier_invoice_lines as lines')
            ->leftJoin('supplier_invoices as invoices', 'invoices.id', '=', 'lines.supplier_invoice_id')
            ->whereNull('invoices.id')
            ->count()
        : 0;

    $orphanSupplierReceiptLines = $seedAuditTableExists('supplier_receipt_lines')
        ? DB::table('supplier_receipt_lines as lines')
            ->leftJoin('supplier_receipts as receipts', 'receipts.id', '=', 'lines.supplier_receipt_id')
            ->whereNull('receipts.id')
            ->count()
        : 0;

    $orphanPaymentAllocations = $seedAuditTableExists('payment_allocations')
        ? DB::table('payment_allocations as allocations')
            ->leftJoin('customer_payments as payments', 'payments.id', '=', 'allocations.customer_payment_id')
            ->leftJoin('notes as notes', 'notes.id', '=', 'allocations.note_id')
            ->where(static function ($query): void {
                $query
                    ->whereNull('payments.id')
                    ->orWhereNull('notes.id');
            })
            ->count()
        : 0;

    $duplicateActiveSupplierInvoiceNormalizedNo = $seedAuditTableExists('supplier_invoices')
        ? DB::query()
            ->fromSub(
                DB::table('supplier_invoices')
                    ->select('nomor_faktur_normalized')
                    ->selectRaw('COUNT(*) as duplicate_count')
                    ->whereNull('voided_at')
                    ->groupBy('nomor_faktur_normalized')
                    ->havingRaw('COUNT(*) > 1'),
                'duplicates'
            )
            ->count()
        : 0;

    $seedAuditPrintLine('orphan supplier invoice lines', $orphanSupplierInvoiceLines);
    $seedAuditPrintLine('orphan supplier receipt lines', $orphanSupplierReceiptLines);
    $seedAuditPrintLine('orphan payment allocations', $orphanPaymentAllocations);
    $seedAuditPrintLine('duplicate active supplier invoice normalized no', $duplicateActiveSupplierInvoiceNormalizedNo);

    foreach ([
        'users total' => $usersTotal === 2,
        'products missing threshold active' => $productsMissingThreshold === 0,
        'SI-BL invoices' => $siBlInvoices === 69,
        'SI-BL versions' => $siBlVersions === 69,
        'SI-BL projections' => $siBlProjections === 69,
        'scenario invoices active' => $scenarioInvoicesActive === 5,
        'void scenario invoices total' => $voidScenarioInvoicesTotal === 3,
        'baseline notes' => $baselineNotes === 240,
        'baseline customer payments' => $baselinePayments === 216,
        'baseline payment allocations' => $baselinePaymentAllocations === 216,
        'baseline refunds' => $baselineRefunds === 12,
        'baseline expenses' => $baselineExpenses === 120,
        'expense categories' => $expenseCategories === 6,
        'orphan supplier invoice lines' => (int) $orphanSupplierInvoiceLines === 0,
        'orphan supplier receipt lines' => (int) $orphanSupplierReceiptLines === 0,
        'orphan payment allocations' => (int) $orphanPaymentAllocations === 0,
        'duplicate active supplier invoice normalized no' => (int) $duplicateActiveSupplierInvoiceNormalizedNo === 0,
    ] as $check => $passed) {
        if (! $passed) {
            $this->error('FAILED: ' . $check);
            $failures++;
        }
    }

    $this->line('');
    $this->line('== RESULT ==');
    $seedAuditPrintLine('failures', $failures);

    return $failures === 0 ? 0 : 1;
})->purpose('Audit deterministic seeder level counts and orphan checks');

Artisan::command('audit:finance', function () use ($seedAuditSum, $seedAuditTableExists, $seedAuditPrintLine): int {
    $failures = 0;

    $this->line('== SCENARIO COVERAGE ==');

    $matrixPath = base_path('docs/handoff/v2/seedernew/2026-04-26-seedernew-scenario-matrix.md');
    $matrixContent = is_file($matrixPath) ? (string) file_get_contents($matrixPath) : '';
    $scenarioCount = preg_match_all('/^\| (Customer|Supplier|Inventory|Expense|Employee|Cash) \|/m', $matrixContent);

    $seedAuditPrintLine('defined scenarios', $scenarioCount);
    $seedAuditPrintLine('missing scenario definitions', $scenarioCount === 60 ? 0 : 60 - $scenarioCount);

    if ($scenarioCount !== 60) {
        $failures++;
    }

    $this->line('');
    $this->line('== CUSTOMER MONEY ==');

    $noteTotal = $seedAuditSum('notes', 'total_rupiah');
    $workItemTotal = $seedAuditSum('work_items', 'subtotal_rupiah');
    $paymentTotal = $seedAuditSum('customer_payments', 'amount_rupiah');
    $paymentAllocationTotal = $seedAuditSum('payment_allocations', 'amount_rupiah');
    $paymentComponentAllocationTotal = $seedAuditSum('payment_component_allocations', 'allocated_amount_rupiah');
    $refundTotal = $seedAuditSum('customer_refunds', 'amount_rupiah');
    $refundComponentTotal = $seedAuditSum('refund_component_allocations', 'refunded_amount_rupiah');

    $overallocatedPayments = $seedAuditTableExists('payment_allocations')
        ? DB::query()
            ->fromSub(
                DB::table('payment_allocations')
                    ->select('customer_payment_id')
                    ->selectRaw('SUM(amount_rupiah) as allocated_total')
                    ->groupBy('customer_payment_id'),
                'allocations'
            )
            ->join('customer_payments as payments', 'payments.id', '=', 'allocations.customer_payment_id')
            ->whereColumn('allocations.allocated_total', '>', 'payments.amount_rupiah')
            ->count()
        : 0;

    $refundOverflow = $seedAuditTableExists('customer_refunds')
        ? DB::query()
            ->fromSub(
                DB::table('customer_refunds')
                    ->select('customer_payment_id')
                    ->selectRaw('SUM(amount_rupiah) as refund_total')
                    ->groupBy('customer_payment_id'),
                'refunds'
            )
            ->join('customer_payments as payments', 'payments.id', '=', 'refunds.customer_payment_id')
            ->whereColumn('refunds.refund_total', '>', 'payments.amount_rupiah')
            ->count()
        : 0;

    $seedAuditPrintLine('note total', $noteTotal);
    $seedAuditPrintLine('work item subtotal', $workItemTotal);
    $seedAuditPrintLine('note/work item mismatch', $workItemTotal - $noteTotal);
    $seedAuditPrintLine('payment total', $paymentTotal);
    $seedAuditPrintLine('payment allocation total', $paymentAllocationTotal);
    $seedAuditPrintLine('payment component allocation total', $paymentComponentAllocationTotal);
    $seedAuditPrintLine('refund total', $refundTotal);
    $seedAuditPrintLine('refund component allocation total', $refundComponentTotal);
    $seedAuditPrintLine('outstanding', $noteTotal - $paymentAllocationTotal + $refundTotal);
    $seedAuditPrintLine('overallocated payments', (int) $overallocatedPayments);
    $seedAuditPrintLine('refund overflow', (int) $refundOverflow);

    foreach ([
        'payment allocation total equals payment total' => $paymentAllocationTotal === $paymentTotal,
        'payment component allocation total equals payment total' => $paymentComponentAllocationTotal === $paymentTotal,
        'refund component allocation total equals refund total' => $refundComponentTotal === $refundTotal,
        'overallocated payments' => (int) $overallocatedPayments === 0,
        'refund overflow' => (int) $refundOverflow === 0,
    ] as $check => $passed) {
        if (! $passed) {
            $this->error('FAILED: ' . $check);
            $failures++;
        }
    }

    $this->line('');
    $this->line('== SUPPLIER MONEY ==');

    $invoiceTotal = $seedAuditSum('supplier_invoices', 'grand_total_rupiah', static fn ($query) => $query->where('lifecycle_status', '!=', 'voided'));
    $invoiceLineTotal = $seedAuditSum('supplier_invoice_lines', 'line_total_rupiah', static fn ($query) => $query->where('is_current', 1));
    $supplierPaymentTotal = $seedAuditSum('supplier_payments', 'amount_rupiah');
    $proofAttachmentCount = $seedAuditTableExists('supplier_payment_proof_attachments')
        ? DB::table('supplier_payment_proof_attachments')->count()
        : 0;

    $voidedActivePayableLeaks = $seedAuditTableExists('supplier_invoice_list_projection')
        ? DB::table('supplier_invoice_list_projection')
            ->where('lifecycle_status', 'voided')
            ->where('outstanding_rupiah', '>', 0)
            ->count()
        : 0;

    $seedAuditPrintLine('invoice total', $invoiceTotal);
    $seedAuditPrintLine('current invoice line total', $invoiceLineTotal);
    $seedAuditPrintLine('supplier payment total', $supplierPaymentTotal);
    $seedAuditPrintLine('payable', $invoiceTotal - $supplierPaymentTotal);
    $seedAuditPrintLine('voided active payable leaks', (int) $voidedActivePayableLeaks);
    $seedAuditPrintLine('payment proof attachment count', (int) $proofAttachmentCount);

    foreach ([
        'supplier invoice total equals current line total' => $invoiceTotal === $invoiceLineTotal,
        'voided active payable leaks' => (int) $voidedActivePayableLeaks === 0,
    ] as $check => $passed) {
        if (! $passed) {
            $this->error('FAILED: ' . $check);
            $failures++;
        }
    }

    $this->line('');
    $this->line('== INVENTORY ==');

    $computedQty = $seedAuditSum('inventory_movements', 'qty_delta');
    $storedQty = $seedAuditSum('product_inventory', 'qty_on_hand');
    $computedValue = $seedAuditSum('inventory_movements', 'total_cost_rupiah');
    $storedValue = $seedAuditSum('product_inventory_costing', 'inventory_value_rupiah');

    $seedAuditPrintLine('computed qty', $computedQty);
    $seedAuditPrintLine('stored qty', $storedQty);
    $seedAuditPrintLine('qty mismatch', $storedQty - $computedQty);
    $seedAuditPrintLine('computed value', $computedValue);
    $seedAuditPrintLine('stored value', $storedValue);
    $seedAuditPrintLine('value mismatch', $storedValue - $computedValue);

    $this->line('');
    $this->line('== EMPLOYEE FINANCE ==');

    $debtTotal = $seedAuditSum('employee_debts', 'total_debt');
    $debtPaid = $seedAuditSum('employee_debt_payments', 'amount');
    $remainingBalance = $seedAuditSum('employee_debts', 'remaining_balance');
    $remainingMismatch = $remainingBalance - ($debtTotal - $debtPaid);

    $seedAuditPrintLine('debt total', $debtTotal);
    $seedAuditPrintLine('debt paid', $debtPaid);
    $seedAuditPrintLine('remaining balance', $remainingBalance);
    $seedAuditPrintLine('remaining mismatch', $remainingMismatch);

    if ($remainingMismatch !== 0) {
        $this->error('FAILED: employee debt remaining mismatch');
        $failures++;
    }

    $this->line('');
    $this->line('== CASH / LEDGER ==');

    $cashIn = $paymentTotal;
    $cashOut = $refundTotal
        + $supplierPaymentTotal
        + $seedAuditSum('operational_expenses', 'amount_rupiah', static fn ($query) => $query->whereNull('deleted_at'))
        + $seedAuditSum('payroll_disbursements', 'amount')
        + $debtPaid;

    $seedAuditPrintLine('cash in', $cashIn);
    $seedAuditPrintLine('cash out', $cashOut);
    $seedAuditPrintLine('net', $cashIn - $cashOut);
    $seedAuditPrintLine('ledger mismatch', 'NOT IMPLEMENTED');

    $this->line('');
    $this->line('== ORPHAN / DUPLICATE ==');

    $orphanPaymentComponents = $seedAuditTableExists('payment_component_allocations')
        ? DB::table('payment_component_allocations as pca')
            ->leftJoin('customer_payments as payments', 'payments.id', '=', 'pca.customer_payment_id')
            ->leftJoin('notes as notes', 'notes.id', '=', 'pca.note_id')
            ->leftJoin('work_items as work_items', 'work_items.id', '=', 'pca.work_item_id')
            ->where(static function ($query): void {
                $query
                    ->whereNull('payments.id')
                    ->orWhereNull('notes.id')
                    ->orWhereNull('work_items.id');
            })
            ->count()
        : 0;

    $orphanRefundComponents = $seedAuditTableExists('refund_component_allocations')
        ? DB::table('refund_component_allocations as rca')
            ->leftJoin('customer_refunds as refunds', 'refunds.id', '=', 'rca.customer_refund_id')
            ->leftJoin('customer_payments as payments', 'payments.id', '=', 'rca.customer_payment_id')
            ->leftJoin('notes as notes', 'notes.id', '=', 'rca.note_id')
            ->leftJoin('work_items as work_items', 'work_items.id', '=', 'rca.work_item_id')
            ->where(static function ($query): void {
                $query
                    ->whereNull('refunds.id')
                    ->orWhereNull('payments.id')
                    ->orWhereNull('notes.id')
                    ->orWhereNull('work_items.id');
            })
            ->count()
        : 0;

    $seedAuditPrintLine('orphan payment component allocations', (int) $orphanPaymentComponents);
    $seedAuditPrintLine('orphan refund component allocations', (int) $orphanRefundComponents);
    $seedAuditPrintLine('duplicate active business keys', 'SEE audit:seed-level 2');

    if ((int) $orphanPaymentComponents !== 0) {
        $this->error('FAILED: orphan payment component allocations');
        $failures++;
    }

    if ((int) $orphanRefundComponents !== 0) {
        $this->error('FAILED: orphan refund component allocations');
        $failures++;
    }

    $this->line('');
    $this->line('== RESULT ==');
    $seedAuditPrintLine('failures', $failures);

    return $failures === 0 ? 0 : 1;
})->purpose('Audit finance reconciliation invariants from source tables');
