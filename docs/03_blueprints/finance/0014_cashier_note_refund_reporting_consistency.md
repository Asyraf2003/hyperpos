# Blueprint 0014 - Cashier Note Refund Reporting Consistency

Status:
Draft / Refund-Reporting Map / No Patch Yet

Links:
- [0038 audit findings](../../04_lifecycle/error_log/0038_cashier_note_create_edit_refund_reporting_audit_findings.md)
- [0011 workflow index](0011_cashier_note_consistency_workflow_index.md)

Scope:
Refund/retur kasir dibanding create dan edit replacement, lalu dampaknya ke transaction summary, cash ledger, Operational Profit, dashboard, dan inventory stock value.

## Refund Flow Map
- Refund full note: should allocate refund across refundable payment components and close/refund note when all active rows are refunded; exact controller path needs re-check.
- Refund selected row: plan bucket records customer_refunds, writes refund_component_allocations, then full-row inventory reversal for supported store-stock components.
- Refund service_only: refund hits service_fee component; no inventory movement.
- Refund service_with_external_purchase: refund can hit external part and service_fee; external purchase cost netting exists in Operational Profit via refund_component_allocations.
- Refund store_stock_sale_only: refund hits product-only component; store stock reversal uses original movement cost.
- Refund service_with_store_stock_part: refund can hit store-stock part components and service_fee.
- Refund package_auto_split: currently follows component model; internal package_profit/base/extra breakdown is not explicit unless source contract is extended.

## Tables
- customer_refunds: records money out/refund event.
- refund_component_allocations: links refund amount to original/current payment components.
- inventory_movements: store-stock refund creates stock_in reversal linked to original work_item_store_stock_line.

## Report Impact
- transaction summary: transaction_date basis from notes, with payments/refunds/refund_due/surplus totals joined per note.
- transaction cash ledger: event_date basis from payments/refunds/surplus refund paid rows.
- operational profit: cash-in by paid_at; refund by refunded_at/effective_date; external cost by transaction_date minus refunded external part by refunded_at; store-stock COGS by inventory movement date.
- dashboard operational performance: same mixed operational/event basis needs re-check per query class.
- inventory stock value: current snapshot from products/product_inventory/product_inventory_costing; not historical transaction report.

## Double Count Risk
- Store-stock reversal should not double count if reversal source already exists.
- External purchase refund netting subtracts refunded external component; report must not also subtract same refund as product cost elsewhere.
- Package refund currently melebur service side ke service_fee; if Service Package Profit Breakdown later splits base/extra/profit, refund allocation rules must avoid counting service_fee and package_profit twice.

## Test Matrix Khusus Refund/Reporting
- full refund service_only marks note refunded.
- selected row refund store_stock_sale_only reverses original unit cost.
- selected row refund service_with_store_stock_part targets current replacement components after edit.
- refund service_with_external_purchase nets external cost consistently.
- refund package_auto_split does not double count service_fee/package profit.
- transaction summary shows refund/refund_due/surplus correctly.
- cash ledger uses refund/payment event dates.
- Operational Profit remains cash-operational formula.
- inventory stock value remains current snapshot and not package profit source.

Evidence:
- Selected-row bucket records refund and calls full-row store-stock reversal: `app/Application/Payment/Services/RecordSelectedRowsRefundPlanBucketProcessor.php:23`, `app/Application/Payment/Services/RecordSelectedRowsRefundPlanBucketProcessor.php:24`, `app/Application/Payment/Services/RecordSelectedRowsRefundPlanBucketProcessor.php:34`
- Refund allocation reads payment_component_allocations and writes refund components: `app/Application/Payment/Services/AllocateRefundAcrossComponents.php:36`, `app/Application/Payment/Services/AllocateRefundAcrossComponents.php:42`, `app/Application/Payment/Services/AllocateRefundAcrossComponents.php:65`
- Store-stock reversal checks existing reversal and reverses source line: `app/Application/Inventory/Services/AutoReverseRefundedStoreStockInventory.php:69`, `app/Application/Inventory/Services/AutoReverseRefundedStoreStockInventory.php:70`, `app/Application/Inventory/Services/AutoReverseRefundedStoreStockInventory.php:74`
- Reverse operation uses original movement unit cost: `app/Application/Inventory/Services/ReverseIssuedInventoryOperation.php:48`, `app/Application/Inventory/Services/ReverseIssuedInventoryOperation.php:77`
- Transaction summary basis and fields: `app/Adapters/Out/Reporting/Queries/TransactionSummaryReportingQuery.php:17`, `app/Adapters/Out/Reporting/Queries/TransactionSummaryReportingQuery.php:31`, `app/Adapters/Out/Reporting/Queries/TransactionSummaryReportingQuery.php:38`, `app/Adapters/Out/Reporting/Queries/TransactionSummaryReportingQuery.php:40`
- Cash ledger event rows: `app/Adapters/Out/Reporting/Queries/TransactionCashLedgerReportingQuery.php:16`, `app/Adapters/Out/Reporting/Queries/TransactionCashLedgerReportingQuery.php:18`, `app/Adapters/Out/Reporting/Queries/TransactionCashLedgerReportingQuery.php:21`
- Operational Profit formula and sources: `app/Adapters/Out/Reporting/Queries/OperationalProfitMetricsQuery.php:20`, `app/Adapters/Out/Reporting/Queries/OperationalProfitMetricsQuery.php:42`, `app/Adapters/Out/Reporting/Queries/OperationalProfit/CashFlowMetricQuery.php:13`, `app/Adapters/Out/Reporting/Queries/OperationalProfit/CashFlowMetricQuery.php:23`, `app/Adapters/Out/Reporting/Queries/OperationalProfit/ProductCostMetricQuery.php:13`, `app/Adapters/Out/Reporting/Queries/OperationalProfit/ProductCostMetricQuery.php:19`, `app/Adapters/Out/Reporting/Queries/OperationalProfit/ProductCostMetricQuery.php:31`
- Inventory stock value current snapshot: `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php:13`, `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php:38`, `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php:39`

Progress Local:
- Status: AUDIT_READY
- Last checked: 2026-06-20
- Next action: Phase 1 refund/report characterization tests.
- Tests linked: ClosedNoteFullRefund*, RecordSelectedRowsCustomerRefundFeatureTest, TransactionSummary*, TransactionCashLedger*, OperationalProfit*.
- Owner decision dependency: package refund internal breakdown and Service Package Profit Breakdown date basis.
