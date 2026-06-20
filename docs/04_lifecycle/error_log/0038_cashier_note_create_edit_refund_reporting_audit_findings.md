# 0038 - Cashier note create/edit/refund/reporting consistency audit findings

Status:
Audit Findings / Owner Decision Required / No Patch Yet

Boundary:
- Web/PWA only.
- Mobile API retired.
- Supplier invoice payment proof out of scope.
- Operational Profit formula tidak diubah.
- Scope ini hanya create/edit/refund/payment/report kasir.

Purpose:
Dokumen ini hanya mencatat finding dan owner decision.
Progress pengerjaan tidak dicatat di file ini.
Progress pengerjaan dicatat di:
[docs/03_blueprints/finance/0011_cashier_note_consistency_workflow_index.md](../../03_blueprints/finance/0011_cashier_note_consistency_workflow_index.md)

Executive Summary:
- Backend create nota kasir punya 4 tipe line aktif:
  1. service_only
  2. service_with_external_purchase
  3. store_stock_sale_only
  4. service_with_store_stock_part
- Edit/revision sebagian besar reuse create mapper/normalizer.
- Refund mengikuti payment_component_allocations dan refund_component_allocations.
- Operational Profit tetap laporan kas operasional, bukan Service Package Profit Breakdown.
- Service Package Profit Breakdown harus report/section terpisah.
- UI/backend dan payload historis punya gap yang harus diputuskan owner sebelum patch.

Finding Groups:

## Group A - UI/backend mismatch create package
Severity: High

Summary:
Backend mendukung service_with_store_stock_part package_auto_split multi-product direct POST, tetapi UI cashier masih membatasi multi-product pada service_store_stock.

Owner decision:
- Buka multi-product package UI sekarang, atau
- kunci single-product secara eksplisit.

## Group B - Template fast-entry vs multi-part package conflict
Severity: High

Summary:
UI/template fast-entry mengarah ke template, sedangkan backend template branch menolak product_lines lebih dari 1.

Owner decision:
- Satu template tetap hanya satu product, atau
- buat package/header template multi-product.

## Group C - Revision payload incomplete for package reporting
Severity: Medium/High

Summary:
work_item_service_details menyimpan package_profit/base/extra, tetapi note_revision_lines.payload belum menyimpan package_profit_rupiah, package_base_service_price_rupiah, package_service_extra_rupiah.

Owner decision:
- revision payload menjadi financial fingerprint, atau
- report cukup membaca active rows + movement + allocation.

## Group D - Correction fee-only package risk
Severity: High

Summary:
Correction fee-only belum package-aware dan bisa membuat package metadata stale.

Owner decision:
- reject correction fee-only untuk package_auto_split, atau
- buat correction package-aware.

## Group E - External purchase package UI gap
Severity: Medium

Summary:
Backend punya external purchase package auto split, tetapi UI belum expose package_total/total_rupiah untuk external package.

Owner decision:
- expose ke UI, atau
- tetap backend-only.

## Group F - Reporting clarity gap
Severity: Medium

Summary:
Operational Profit bukan Service Package Profit Breakdown.

Owner decision:
- buat Service Package Profit Breakdown terpisah setelah source data dikunci.

## Group G - Refund package breakdown gap
Severity: Medium

Summary:
Refund store-stock normal sudah reverse original COGS, tetapi refund package belum eksplisit memecah package_profit/package_service_extra.

Owner decision:
- package refund cukup melebur ke service_fee, atau
- perlu breakdown internal service base/extra/profit.

Owner Decision Checklist:
- [ ] Multi-product package UI dibuka sekarang atau ditunda.
- [ ] Template branch tetap single-product atau dibuat package template multi-product.
- [ ] Correction fee-only untuk package_auto_split ditolak atau dibuat package-aware.
- [ ] Revision payload wajib menyimpan package fields atau tidak.
- [ ] External purchase package auto split diekspos di UI atau tidak.
- [ ] Service Package Profit Breakdown basis tanggal memakai transaction_date, payment_date, refund_date, atau kombinasi.
- [ ] Refund package breakdown melebur ke service_fee atau dipecah internal.

Blueprint Map:
- [0011_cashier_note_consistency_workflow_index.md](../../03_blueprints/finance/0011_cashier_note_consistency_workflow_index.md)
- [0012_cashier_note_create_line_source_map.md](../../03_blueprints/finance/0012_cashier_note_create_line_source_map.md)
- [0013_cashier_note_edit_revision_payment_consistency.md](../../03_blueprints/finance/0013_cashier_note_edit_revision_payment_consistency.md)
- [0014_cashier_note_refund_reporting_consistency.md](../../03_blueprints/finance/0014_cashier_note_refund_reporting_consistency.md)
- [0015_service_package_profit_breakdown_source_contract.md](../../03_blueprints/finance/0015_service_package_profit_breakdown_source_contract.md)

Evidence:
- Web/PWA boundary and Mobile API retired: `docs/04_lifecycle/error_log/0035_mobile_api_retired_pwa_runtime_boundary.md:9`, `docs/04_lifecycle/error_log/0035_mobile_api_retired_pwa_runtime_boundary.md:29`
- Supplier invoice payment proof is separate Web/PWA scope: `docs/04_lifecycle/error_log/0036_supplier_invoice_payment_proof_web_pwa_audit_findings.md:7`
- Operational Profit formula boundary: `docs/04_lifecycle/error_log/0037_profit_calculation_logic_audit_findings.md:9`, `docs/04_lifecycle/error_log/0037_profit_calculation_logic_audit_findings.md:11`, `docs/04_lifecycle/error_log/0037_profit_calculation_logic_audit_findings.md:18`
- Four work item types exist in domain: `app/Core/Note/WorkItem/WorkItem.php:12`, `app/Core/Note/WorkItem/WorkItem.php:13`, `app/Core/Note/WorkItem/WorkItem.php:14`, `app/Core/Note/WorkItem/WorkItem.php:15`
- Create mapper selects product, external, store-stock, or service-only branches: `app/Application/Note/Services/CreateTransactionWorkspaceWorkItemPayloadMapper.php:29`, `app/Application/Note/Services/CreateTransactionWorkspaceWorkItemPayloadMapper.php:54`, `app/Application/Note/Services/CreateTransactionWorkspaceWorkItemPayloadMapper.php:58`, `app/Application/Note/Services/CreateTransactionWorkspaceWorkItemPayloadMapper.php:62`
- Request accepts `manual_split`, `package_auto_split`, `package_total_rupiah`, product lines, external total, and inline payment: `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRules.php:26`, `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRules.php:28`, `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRules.php:35`, `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRules.php:44`, `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRules.php:46`
- UI service_store_stock currently carries template requirement and package total: `resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php:14`, `resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php:15`, `resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php:140`
- UI JS limits/preloads service_store_stock product lines to one: `public/assets/static/js/pages/cashier-note-workspace/rows.js:80`, `public/assets/static/js/pages/cashier-note-workspace/rows.js:81`, `public/assets/static/js/pages/cashier-note-workspace/rows.js:268`
- Template package branch rejects multiple product lines and stores package fields: `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:22`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:38`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:39`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:40`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches.php:41`
- External package backend composer exists: `app/Application/Note/Services/CreateTransactionWorkspaceServiceExternalPurchasePackagePricingComposer.php:19`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceExternalPurchasePackagePricingComposer.php:30`, `app/Application/Note/Services/CreateTransactionWorkspaceServiceExternalPurchasePackagePricingComposer.php:37`
- Revision builder reuses create mapper: `app/Application/Note/UseCases/CreateNoteRevisionPayloadWorkItemBuilder.php:15`, `app/Application/Note/UseCases/CreateNoteRevisionPayloadWorkItemBuilder.php:35`
- Revision payload has package total/parts/service price but not package profit/base/extra in inspected mapper: `app/Application/Note/Services/NoteRevisionLinePayloadMapper.php:61`, `app/Application/Note/Services/NoteRevisionLinePayloadMapper.php:62`, `app/Application/Note/Services/NoteRevisionLinePayloadMapper.php:63`, `app/Application/Note/Services/NoteRevisionLinePayloadMapper.php:64`
- Edit replacement reverses inventory, deletes/replaces work items, and rebuilds payment allocations: `app/Application/Note/Services/UpdateTransactionWorkspaceWorkItemPersister.php:25`, `app/Application/Note/Services/UpdateTransactionWorkspaceWorkItemPersister.php:31`, `app/Application/Note/Services/ApplyNoteRevisionAsActiveReplacement.php:43`, `app/Application/Note/Services/ApplyNoteRevisionAsActiveReplacement.php:46`
- Refund allocates through payment components and writes refund component allocations: `app/Application/Payment/Services/AllocateRefundAcrossComponents.php:36`, `app/Application/Payment/Services/AllocateRefundAcrossComponents.php:65`
- Store-stock refund reversal uses original movement unit cost: `app/Application/Inventory/Services/ReverseIssuedInventoryOperation.php:48`, `app/Application/Inventory/Services/ReverseIssuedInventoryOperation.php:77`
