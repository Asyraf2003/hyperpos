# Blueprint 0015 - Service Package Profit Breakdown Source Contract

Status:
Draft / Source Contract / No Patch Yet

Links:
- [0038 audit findings](../../04_lifecycle/error_log/0038_cashier_note_create_edit_refund_reporting_audit_findings.md)
- [0011 workflow index](0011_cashier_note_consistency_workflow_index.md)

Scope:
Service Package Profit Breakdown adalah report terpisah dari Operational Profit. Operational Profit tetap laporan kas operasional dan formulanya tidak diubah.

## Source Data
- work_items: package row identity, transaction type, active/canceled/refunded status.
- work_item_service_details: service price, package_profit_rupiah, package_base_service_price_rupiah, package_service_extra_rupiah.
- work_item_store_stock_lines: sparepart sales line totals.
- work_item_external_purchase_lines: external purchase line totals for external package branch.
- note_revision_lines payload: candidate historical fingerprint after Phase 3 decision.
- payment_component_allocations: paid component allocation by current replacement component.
- refund_component_allocations: refunded component allocation and netting source.
- inventory_movements: store-stock original COGS and reversal COGS.

## Field Minimal
- package_total_rupiah
- sparepart_sales_total_rupiah
- sparepart_cogs_rupiah
- sparepart_margin_rupiah
- base_service_price_rupiah
- package_service_extra_rupiah
- package_profit_rupiah
- total_service_component_rupiah
- total_package_gross_profit_rupiah

## Formula Candidate
- package_total_rupiah = final customer package total from historical package row.
- sparepart_sales_total_rupiah = sum store_stock line totals for package rows.
- sparepart_cogs_rupiah = sum ABS stock_out total_cost_rupiah for linked store_stock lines minus linked reversal costs, depending on selected date basis.
- sparepart_margin_rupiah = sparepart_sales_total_rupiah - sparepart_cogs_rupiah.
- base_service_price_rupiah = package_base_service_price_rupiah if present; otherwise owner decision required for non-template branch.
- package_service_extra_rupiah = package_service_extra_rupiah if present; otherwise 0.
- package_profit_rupiah = package_profit_rupiah if present; otherwise 0.
- total_service_component_rupiah = service_price_rupiah + package_profit_rupiah, or another owner-approved final formula.
- total_package_gross_profit_rupiah = sparepart_margin_rupiah + total_service_component_rupiah.

## Basis Tanggal Decision
Owner must choose one:
- transaction_date: best for package sold performance.
- payment_date: best for cash-realized package performance.
- refund_date: needed for refund event view.
- combination: transaction basis with separate cash/refund columns.

No patch may start for Phase 5 until this basis is locked.

## Historical Snapshot Rules
- Historical money must come from work item rows, service detail package fields, note revision payload, payment/refund allocations, or inventory movements.
- Product current price must not change old package sales.
- Product current name may be display-only; report money cannot depend on it.
- Current AVG/modal must not change historical COGS.
- Service catalog/template current defaults must not change old package service values.
- Refund package breakdown must not double count service_fee and package_profit.

## Current Master Data Leak Risks
- Inventory current snapshot joins products/current costing and is not a historical package profit source.
- Revision payload currently lacks package_profit/base/extra in inspected mapper, so using payload alone would under-spec template package profit.
- UI/backend mismatch can create different package shapes unless Phase 4 locks the contract.
- External package backend exists, but UI exposure and report formula must be locked before showing it as supported cashier workflow.

## Required Tests Before Report UI
- normal template package.
- non-template package.
- multi-product package.
- external package if enabled.
- package after edit upward and downward.
- package after selected-row refund.
- package after product price update.
- package after AVG update.
- package after service template update.
- basis tanggal behavior across transaction/payment/refund/movement dates.
- no Operational Profit formula regression.

Evidence:
- Operational Profit separation: `docs/04_lifecycle/error_log/0037_profit_calculation_logic_audit_findings.md:9`, `docs/04_lifecycle/error_log/0037_profit_calculation_logic_audit_findings.md:23`, `docs/04_lifecycle/error_log/0037_profit_calculation_logic_audit_findings.md:27`, `docs/04_lifecycle/error_log/0037_profit_calculation_logic_audit_findings.md:72`
- Existing package fields in create mapper/service detail writer: `app/Application/Note/Services/CreateTransactionWorkspaceWorkItemPayloadMapper.php:48`, `app/Application/Note/Services/CreateTransactionWorkspaceWorkItemPayloadMapper.php:49`, `app/Application/Note/Services/CreateTransactionWorkspaceWorkItemPayloadMapper.php:50`, `app/Adapters/Out/Note/DatabaseWorkItemWriterAdapter.php:39`, `app/Adapters/Out/Note/DatabaseWorkItemWriterAdapter.php:40`, `app/Adapters/Out/Note/DatabaseWorkItemWriterAdapter.php:41`
- Template branch package field calculation: `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:38`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:39`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:40`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:41`
- Non-template branch package fields: `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:57`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:69`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:70`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:71`
- Payment/refund component source: `app/Application/Payment/Services/PayableComponentsFromWorkItem.php:36`, `app/Application/Payment/Services/PayableComponentsFromWorkItem.php:50`, `app/Application/Payment/Services/AllocateRefundAcrossComponents.php:36`, `app/Application/Payment/Services/AllocateRefundAcrossComponents.php:65`
- Inventory movement COGS source: `app/Adapters/Out/Reporting/Queries/OperationalProfit/ProductCostMetricQuery.php:31`, `app/Application/Inventory/Services/ReverseIssuedInventoryOperation.php:77`
- Current inventory snapshot leak risk: `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php:13`, `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php:38`, `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php:39`

Progress Local:
- Status: DECISION_REQUIRED
- Last checked: 2026-06-20
- Next action: Owner locks basis tanggal and service component formula before Phase 5.
- Tests linked: future ServicePackageProfitBreakdown query tests; OperationalProfit regression tests.
- Owner decision dependency: basis tanggal, total_service_component formula, revision payload fingerprint, refund package breakdown.
