# Blueprint 0011 - Cashier Note Consistency Workflow Index

Status:
Draft / Workflow Index / No Patch Yet

Purpose:
File ini adalah progress ledger utama untuk pekerjaan create/edit/refund/payment/report kasir.
Semua progress phase dicatat di file ini.

Related docs:
- [0038_cashier_note_create_edit_refund_reporting_audit_findings.md](../../04_lifecycle/error_log/0038_cashier_note_create_edit_refund_reporting_audit_findings.md)
- [0012_cashier_note_create_line_source_map.md](0012_cashier_note_create_line_source_map.md)
- [0013_cashier_note_edit_revision_payment_consistency.md](0013_cashier_note_edit_revision_payment_consistency.md)
- [0014_cashier_note_refund_reporting_consistency.md](0014_cashier_note_refund_reporting_consistency.md)
- [0015_service_package_profit_breakdown_source_contract.md](0015_service_package_profit_breakdown_source_contract.md)

Progress Marker Convention:
Gunakan status berikut untuk setiap phase:

- TODO: belum mulai.
- AUDIT_READY: scope dan evidence cukup untuk mulai.
- DECISION_REQUIRED: butuh keputusan owner sebelum patch.
- TEST_DESIGNED: test plan sudah ditulis.
- RED_READY: RED test siap/atau sudah terbukti gagal.
- PATCH_READY: patch boleh mulai.
- IN_PROGRESS: sedang dieksekusi.
- BLOCKED: tertahan karena dependency/decision/test.
- VERIFYING: patch selesai, test sedang diverifikasi.
- FIXED: phase selesai dan targeted tests pass, atau docs-only phase selesai tanpa test.
- DEFERRED: sengaja ditunda.
- REJECTED: keputusan owner menolak phase.

Progress Ledger:

| Phase | Status | Owner Decision | Target Docs | Target Source | Target Tests | Last Evidence | Next Action | Stop Condition |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Phase 0 Docs lock | FIXED | Not required | 0038, 0011, 0012, 0013, 0014, 0015 | None | None | File docs created in docs-only phase | Owner review findings and decisions | Semua file dibuat; progress ledger ada; owner checklist ada |
| Phase 1 Characterization tests only | TODO | Not required to design tests; required before patch decisions harden | 0011-0015 | None | See Phase 1 list | needs re-check | Write characterization tests only | RED/GREEN characterization jelas |
| Phase 2 Hardening guards | TODO | Required for template, correction, external package gates | 0011, 0012, 0013 | Package/correction guard candidates | Guard tests | needs re-check | Wait owner decision and Phase 1 proof | targeted tests pass |
| Phase 3 Revision payload historical fingerprint | TODO | Required: revision payload financial fingerprint or active rows/movement/allocation only | 0011, 0013, 0015 | NoteRevisionLinePayloadMapper candidate | Revision payload tests | `app/Application/Note/Services/NoteRevisionLinePayloadMapper.php:61` | Decide payload contract | revision payload can become financial fingerprint |
| Phase 4 UI create/update consistency | TODO | Required: open multi-product UI or explicit single-product lock | 0011, 0012 | Blade/JS workspace candidates | Page/submit contract tests | `public/assets/static/js/pages/cashier-note-workspace/rows.js:81` | Decide UI contract | UI and backend contract sama |
| Phase 5 Service Package Profit Breakdown query | TODO | Required: basis tanggal and service component formula | 0011, 0015 | Query/source candidate only | Query tests | `docs/04_lifecycle/error_log/0037_profit_calculation_logic_audit_findings.md:23` | Lock source contract | query reconciled and no mutable master leak for money |
| Phase 6 Report UI/export | TODO | Required after Phase 5 | 0011, 0015 | UI/export candidates | Page/export tests | needs re-check | Wait Phase 5 | UI/export pass targeted tests |
| Phase 7 Regression matrix | TODO | Not required unless prior phases accepted | 0011 | None or phase patches | Focused suites | needs re-check | Build final regression command index | focused suites green |

Workflow Rules:
- Tidak boleh naik phase sebelum stop condition phase sebelumnya terpenuhi.
- Patch source hanya boleh mulai dari Phase 2.
- Phase 1 hanya test/characterization.
- Phase 3 tidak boleh jalan sebelum owner decision soal revision payload.
- Phase 5 tidak boleh jalan sebelum source contract di 0015 dikunci.
- Operational Profit formula tidak diubah.
- Payment proof supplier invoice tetap out of scope.
- Mobile API tetap retired.

Phase Roadmap:

## Phase 0 - Docs Lock
Goal:
- Buat 0038 dan blueprint 0011-0015.
- Tidak patch source.

Target docs:
- 0038
- 0011
- 0012
- 0013
- 0014
- 0015

Tests:
- Tidak ada.

Stop condition:
- Semua file dibuat.
- Progress ledger ada.
- Owner decision checklist ada.

## Phase 1 - Characterization Tests Only
Goal:
- Membuktikan current behavior dan gap.

Target tests:
- create 4 tipe line subtotal benar.
- create package_auto_split single-product.
- create package_auto_split multi-product backend.
- UI contract multi-product service_store_stock.
- external purchase package backend behavior.
- edit naik -> underpaid/outstanding.
- edit turun -> overpaid/surplus/refund_due.
- edit package preserves/rebuilds payment allocations.
- refund store-stock uses original unit cost.
- refund package selected row tidak target stale old components.
- admin update product price tidak mengubah historical line totals.
- admin update AVG tidak mengubah historical COGS.

Stop condition:
- RED/GREEN characterization jelas.

## Phase 2 - Hardening Guards
Goal:
- Cegah jalur yang diam-diam merusak data.

Patch candidates:
- reject correction fee-only untuk package_auto_split.
- explicit guard/copy jika template package tetap single-product.
- explicit guard kalau external package belum didukung UI.

Tests:
- correction fee-only package rejected.
- non-package correction fee-only tetap pass.
- template multi-product behavior sesuai owner decision.

Stop condition:
- targeted tests pass.

## Phase 3 - Revision Payload Historical Fingerprint
Goal:
- Lengkapi snapshot historis untuk package breakdown.

Patch candidates:
- NoteRevisionLinePayloadMapper menulis package_profit_rupiah.
- menulis package_base_service_price_rupiah.
- menulis package_service_extra_rupiah.
- menulis package_total/parts_total/service component final.

Tests:
- edit setelah master data berubah tetap memakai snapshot.
- template package revision payload lengkap.

Stop condition:
- revision payload dapat menjadi financial fingerprint.

## Phase 4 - UI Create/Update Consistency
Goal:
- Selaraskan UI dengan backend.

Patch candidates:
- buka multi-product package untuk non-template, atau
- kunci single-product dengan copy/validation jelas.
- expose external purchase package jika owner pilih.

Tests:
- page contract.
- submit multi-product package.
- submit external package jika enabled.

Stop condition:
- UI dan backend contract sama.

## Phase 5 - Service Package Profit Breakdown Query
Goal:
- Query/report source tanpa UI dulu.

Formula candidate:
- sparepart_sales_total_rupiah = sum store_stock line totals.
- sparepart_cogs_rupiah = sum ABS inventory movement total_cost for linked store_stock lines.
- sparepart_margin_rupiah = sparepart_sales_total - sparepart_cogs.
- total_service_component_rupiah = service_price + package_profit, atau sesuai owner decision final.
- total_package_gross_profit_rupiah = sparepart_margin + total_service_component.

Tests:
- normal package.
- package after edit.
- package after refund.
- product price changed after note.
- AVG changed after note.
- multi-product package.
- non-template package.

Stop condition:
- query angka reconciled dan tidak membaca current mutable master untuk historical money.

## Phase 6 - Report UI/Export
Goal:
- Surface Service Package Profit Breakdown.

Tests:
- page renders.
- date filter.
- Excel/PDF jika diperlukan.
- tidak bentrok dengan Operational Profit naming.

Stop condition:
- UI/export pass targeted tests.

## Phase 7 - Regression Matrix
Goal:
- Protect create/edit/refund/payment/report invariants.

Test command index:
- `php artisan test --filter=CreateTransactionWorkspace`
- `php artisan test --filter=EditTransactionWorkspacePackageAutoSplitCharacterizationTest`
- `php artisan test --filter=CorrectPaidServiceWithStoreStockPartServiceFeeOnlyFeatureTest`
- `php artisan test --filter=ClosedNoteFullRefund`
- `php artisan test --filter=RecordSelectedRowsCustomerRefundFeatureTest`
- `php artisan test --filter=TransactionSummary`
- `php artisan test --filter=TransactionCashLedger`
- `php artisan test --filter=OperationalProfit`

Stop condition:
- focused suites green.
