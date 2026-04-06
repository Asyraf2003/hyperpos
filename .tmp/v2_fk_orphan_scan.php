<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$checks = [
    [
        'name' => 'supplier_invoices.supplier_id -> suppliers.id',
        'childTable' => 'supplier_invoices',
        'childPk' => 'id',
        'childColumn' => 'supplier_id',
        'parentTable' => 'suppliers',
        'parentPk' => 'id',
    ],
    [
        'name' => 'supplier_invoice_lines.supplier_invoice_id -> supplier_invoices.id',
        'childTable' => 'supplier_invoice_lines',
        'childPk' => 'id',
        'childColumn' => 'supplier_invoice_id',
        'parentTable' => 'supplier_invoices',
        'parentPk' => 'id',
    ],
    [
        'name' => 'supplier_invoice_lines.product_id -> products.id',
        'childTable' => 'supplier_invoice_lines',
        'childPk' => 'id',
        'childColumn' => 'product_id',
        'parentTable' => 'products',
        'parentPk' => 'id',
    ],
    [
        'name' => 'supplier_receipts.supplier_invoice_id -> supplier_invoices.id',
        'childTable' => 'supplier_receipts',
        'childPk' => 'id',
        'childColumn' => 'supplier_invoice_id',
        'parentTable' => 'supplier_invoices',
        'parentPk' => 'id',
    ],
    [
        'name' => 'supplier_receipt_lines.supplier_receipt_id -> supplier_receipts.id',
        'childTable' => 'supplier_receipt_lines',
        'childPk' => 'id',
        'childColumn' => 'supplier_receipt_id',
        'parentTable' => 'supplier_receipts',
        'parentPk' => 'id',
    ],
    [
        'name' => 'supplier_receipt_lines.supplier_invoice_line_id -> supplier_invoice_lines.id',
        'childTable' => 'supplier_receipt_lines',
        'childPk' => 'id',
        'childColumn' => 'supplier_invoice_line_id',
        'parentTable' => 'supplier_invoice_lines',
        'parentPk' => 'id',
    ],
    [
        'name' => 'inventory_movements.product_id -> products.id',
        'childTable' => 'inventory_movements',
        'childPk' => 'id',
        'childColumn' => 'product_id',
        'parentTable' => 'products',
        'parentPk' => 'id',
    ],
    [
        'name' => 'product_inventory.product_id -> products.id',
        'childTable' => 'product_inventory',
        'childPk' => 'product_id',
        'childColumn' => 'product_id',
        'parentTable' => 'products',
        'parentPk' => 'id',
    ],
    [
        'name' => 'product_inventory_costing.product_id -> products.id',
        'childTable' => 'product_inventory_costing',
        'childPk' => 'product_id',
        'childColumn' => 'product_id',
        'parentTable' => 'products',
        'parentPk' => 'id',
    ],
    [
        'name' => 'supplier_payments.supplier_invoice_id -> supplier_invoices.id',
        'childTable' => 'supplier_payments',
        'childPk' => 'id',
        'childColumn' => 'supplier_invoice_id',
        'parentTable' => 'supplier_invoices',
        'parentPk' => 'id',
    ],
    [
        'name' => 'supplier_payment_proof_attachments.supplier_payment_id -> supplier_payments.id',
        'childTable' => 'supplier_payment_proof_attachments',
        'childPk' => 'id',
        'childColumn' => 'supplier_payment_id',
        'parentTable' => 'supplier_payments',
        'parentPk' => 'id',
    ],
    [
        'name' => 'work_items.note_id -> notes.id',
        'childTable' => 'work_items',
        'childPk' => 'id',
        'childColumn' => 'note_id',
        'parentTable' => 'notes',
        'parentPk' => 'id',
    ],
    [
        'name' => 'work_item_service_details.work_item_id -> work_items.id',
        'childTable' => 'work_item_service_details',
        'childPk' => 'work_item_id',
        'childColumn' => 'work_item_id',
        'parentTable' => 'work_items',
        'parentPk' => 'id',
    ],
    [
        'name' => 'work_item_external_purchase_lines.work_item_id -> work_items.id',
        'childTable' => 'work_item_external_purchase_lines',
        'childPk' => 'id',
        'childColumn' => 'work_item_id',
        'parentTable' => 'work_items',
        'parentPk' => 'id',
    ],
    [
        'name' => 'work_item_store_stock_lines.work_item_id -> work_items.id',
        'childTable' => 'work_item_store_stock_lines',
        'childPk' => 'id',
        'childColumn' => 'work_item_id',
        'parentTable' => 'work_items',
        'parentPk' => 'id',
    ],
    [
        'name' => 'work_item_store_stock_lines.product_id -> products.id',
        'childTable' => 'work_item_store_stock_lines',
        'childPk' => 'id',
        'childColumn' => 'product_id',
        'parentTable' => 'products',
        'parentPk' => 'id',
    ],
    [
        'name' => 'payment_allocations.customer_payment_id -> customer_payments.id',
        'childTable' => 'payment_allocations',
        'childPk' => 'id',
        'childColumn' => 'customer_payment_id',
        'parentTable' => 'customer_payments',
        'parentPk' => 'id',
    ],
    [
        'name' => 'payment_allocations.note_id -> notes.id',
        'childTable' => 'payment_allocations',
        'childPk' => 'id',
        'childColumn' => 'note_id',
        'parentTable' => 'notes',
        'parentPk' => 'id',
    ],
    [
        'name' => 'customer_refunds.customer_payment_id -> customer_payments.id',
        'childTable' => 'customer_refunds',
        'childPk' => 'id',
        'childColumn' => 'customer_payment_id',
        'parentTable' => 'customer_payments',
        'parentPk' => 'id',
    ],
    [
        'name' => 'customer_refunds.note_id -> notes.id',
        'childTable' => 'customer_refunds',
        'childPk' => 'id',
        'childColumn' => 'note_id',
        'parentTable' => 'notes',
        'parentPk' => 'id',
    ],
    [
        'name' => 'payment_component_allocations.customer_payment_id -> customer_payments.id',
        'childTable' => 'payment_component_allocations',
        'childPk' => 'id',
        'childColumn' => 'customer_payment_id',
        'parentTable' => 'customer_payments',
        'parentPk' => 'id',
    ],
    [
        'name' => 'payment_component_allocations.note_id -> notes.id',
        'childTable' => 'payment_component_allocations',
        'childPk' => 'id',
        'childColumn' => 'note_id',
        'parentTable' => 'notes',
        'parentPk' => 'id',
    ],
    [
        'name' => 'payment_component_allocations.work_item_id -> work_items.id',
        'childTable' => 'payment_component_allocations',
        'childPk' => 'id',
        'childColumn' => 'work_item_id',
        'parentTable' => 'work_items',
        'parentPk' => 'id',
    ],
    [
        'name' => 'refund_component_allocations.customer_refund_id -> customer_refunds.id',
        'childTable' => 'refund_component_allocations',
        'childPk' => 'id',
        'childColumn' => 'customer_refund_id',
        'parentTable' => 'customer_refunds',
        'parentPk' => 'id',
    ],
    [
        'name' => 'refund_component_allocations.customer_payment_id -> customer_payments.id',
        'childTable' => 'refund_component_allocations',
        'childPk' => 'id',
        'childColumn' => 'customer_payment_id',
        'parentTable' => 'customer_payments',
        'parentPk' => 'id',
    ],
    [
        'name' => 'refund_component_allocations.note_id -> notes.id',
        'childTable' => 'refund_component_allocations',
        'childPk' => 'id',
        'childColumn' => 'note_id',
        'parentTable' => 'notes',
        'parentPk' => 'id',
    ],
    [
        'name' => 'refund_component_allocations.work_item_id -> work_items.id',
        'childTable' => 'refund_component_allocations',
        'childPk' => 'id',
        'childColumn' => 'work_item_id',
        'parentTable' => 'work_items',
        'parentPk' => 'id',
    ],
    [
        'name' => 'note_mutation_events.note_id -> notes.id',
        'childTable' => 'note_mutation_events',
        'childPk' => 'id',
        'childColumn' => 'note_id',
        'parentTable' => 'notes',
        'parentPk' => 'id',
    ],
    [
        'name' => 'note_mutation_events.related_customer_payment_id -> customer_payments.id',
        'childTable' => 'note_mutation_events',
        'childPk' => 'id',
        'childColumn' => 'related_customer_payment_id',
        'parentTable' => 'customer_payments',
        'parentPk' => 'id',
    ],
    [
        'name' => 'note_mutation_events.related_customer_refund_id -> customer_refunds.id',
        'childTable' => 'note_mutation_events',
        'childPk' => 'id',
        'childColumn' => 'related_customer_refund_id',
        'parentTable' => 'customer_refunds',
        'parentPk' => 'id',
    ],
    [
        'name' => 'note_mutation_snapshots.note_mutation_event_id -> note_mutation_events.id',
        'childTable' => 'note_mutation_snapshots',
        'childPk' => 'id',
        'childColumn' => 'note_mutation_event_id',
        'parentTable' => 'note_mutation_events',
        'parentPk' => 'id',
    ],
    [
        'name' => 'transaction_workspace_drafts.note_id -> notes.id',
        'childTable' => 'transaction_workspace_drafts',
        'childPk' => 'id',
        'childColumn' => 'note_id',
        'parentTable' => 'notes',
        'parentPk' => 'id',
    ],
];

$defaultConnection = (string) config('database.default');
$databaseName = (string) config("database.connections.{$defaultConnection}.database");

$results = [];
$failedChecks = 0;
$nonEmptyChildChecks = 0;

foreach ($checks as $check) {
    $childRowCount = (int) DB::table($check['childTable'])->count();
    $parentRowCount = (int) DB::table($check['parentTable'])->count();

    if ($childRowCount > 0) {
        $nonEmptyChildChecks++;
    }

    $baseQuery = DB::table($check['childTable'] . ' as c')
        ->leftJoin(
            $check['parentTable'] . ' as p',
            'c.' . $check['childColumn'],
            '=',
            'p.' . $check['parentPk']
        )
        ->whereNotNull('c.' . $check['childColumn'])
        ->whereNull('p.' . $check['parentPk']);

    $orphanCount = (int) (clone $baseQuery)->count();

    $samples = [];
    foreach (
        (clone $baseQuery)
            ->selectRaw(
                'c.' . $check['childPk'] . ' as child_id, ' .
                'c.' . $check['childColumn'] . ' as missing_parent_id'
            )
            ->limit(10)
            ->get() as $row
    ) {
        $samples[] = [
            'child_id' => (string) $row->child_id,
            'missing_parent_id' => (string) $row->missing_parent_id,
        ];
    }

    if ($orphanCount > 0) {
        $failedChecks++;
    }

    $results[] = [
        'name' => $check['name'],
        'child_table' => $check['childTable'],
        'child_column' => $check['childColumn'],
        'parent_table' => $check['parentTable'],
        'parent_pk' => $check['parentPk'],
        'child_row_count' => $childRowCount,
        'parent_row_count' => $parentRowCount,
        'orphan_count' => $orphanCount,
        'samples' => $samples,
    ];
}

$output = [
    'generated_at' => date(DATE_ATOM),
    'connection' => $defaultConnection,
    'driver' => DB::connection()->getDriverName(),
    'database' => $databaseName,
    'total_checks' => count($checks),
    'non_empty_child_checks' => $nonEmptyChildChecks,
    'failed_checks' => $failedChecks,
    'overall_status' => $failedChecks === 0 ? 'PASS' : 'FAIL',
    'results' => $results,
];

$reportPath = __DIR__ . '/v2_fk_orphan_scan_report.json';

file_put_contents(
    $reportPath,
    json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo 'overall_status=' . $output['overall_status'] . PHP_EOL;
echo 'failed_checks=' . $failedChecks . PHP_EOL;
echo 'non_empty_child_checks=' . $nonEmptyChildChecks . PHP_EOL;
echo 'report=' . $reportPath . PHP_EOL;

if ($nonEmptyChildChecks === 0) {
    echo 'warning=all scanned child tables have 0 rows; restore a real v1 backup before trusting this report.' . PHP_EOL;
}

foreach ($results as $result) {
    if ($result['orphan_count'] > 0) {
        echo $result['name'] . ' => orphan_count=' . $result['orphan_count'] . PHP_EOL;
    }
}
