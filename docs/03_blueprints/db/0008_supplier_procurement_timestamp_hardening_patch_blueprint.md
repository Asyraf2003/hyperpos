# DB Blueprint 0008 - Supplier Procurement Timestamp Hardening Patch Blueprint

Status: Patch Blueprinted
Scope: `supplier_invoices`, `supplier_receipts`, and `supplier_payments` row timestamp hardening
Owner: HyperPOS

## 1. Active Table Group

Table groups:

- `supplier_invoices`
- `supplier_receipts`
- `supplier_payments`

Category:

- Supplier invoice root/header table
- Supplier receipt root/header table
- Supplier payment source table

Source-of-truth status:

- P0 supplier payable/source header
- P0 stock/procurement receipt source
- P0 supplier payable payment source

## 2. Exact Problem

Supplier/procurement root rows are finance-sensitive and operationally important rows.

Current proven gap:

- `supplier_invoices.created_at` is not present in the root migration.
- `supplier_invoices.updated_at` is not present in the root migration.
- `supplier_receipts.created_at` is not present in the root migration.
- `supplier_receipts.updated_at` is not present in the root migration.
- `supplier_payments.created_at` is not present in the root migration.
- `supplier_payments.updated_at` is not present in the root migration.
- Supplier invoice, receipt, and payment writers are not yet proven to write system row timestamps.

Risk:

- Adding non-null timestamps without nullable compatibility can break direct inserts across tests and seeders.
- Treating row timestamps as business/report dates would corrupt procurement reporting semantics.
- Adding timestamp indexes without read-path proof can create unnecessary index bloat.
- Changing FK/delete semantics can break procurement, receipt, payment proof, reversal, and reporting flows.
- Changing supplier payable/payment/receipt math can break outstanding payable reports.

## 3. Current Proven Schema

Supplier invoice migration:

- `database/migrations/2026_03_12_000200_create_supplier_invoices_table.php`

Proven `supplier_invoices` columns:

- `id`
- `supplier_id`
- `supplier_nama_pt_pengirim_snapshot`
- `nomor_faktur`
- `nomor_faktur_normalized`
- `document_kind`
- `lifecycle_status`
- `origin_supplier_invoice_id`
- `superseded_by_supplier_invoice_id`
- `tanggal_pengiriman`
- `jatuh_tempo`
- `grand_total_rupiah`
- `voided_at`
- `void_reason`
- `last_revision_no`

Proven indexes:

- `supplier_id`
- `tanggal_pengiriman`
- `jatuh_tempo`
- `nomor_faktur_normalized`
- `lifecycle_status`, `tanggal_pengiriman`
- `lifecycle_status`, `jatuh_tempo`
- `origin_supplier_invoice_id`
- `superseded_by_supplier_invoice_id`

Supplier receipt migration:

- `database/migrations/2026_03_12_000400_create_supplier_receipts_table.php`

Proven `supplier_receipts` columns:

- `id`
- `supplier_invoice_id`
- `tanggal_terima`

Proven indexes:

- `supplier_invoice_id`
- `tanggal_terima`

Supplier payment migration:

- `database/migrations/2026_03_12_000800_create_supplier_payments_table.php`

Proven `supplier_payments` columns:

- `id`
- `supplier_invoice_id`
- `amount_rupiah`
- `paid_at`
- `proof_status`
- `proof_storage_path`

Proven indexes:

- `supplier_invoice_id`
- `paid_at`
- `proof_status`

## 4. Current Proven Foreign Key Behavior

FK migration:

- `database/migrations/2026_04_06_220100_add_v2_procurement_inventory_foreign_keys.php`

Proven FK behavior:

- `supplier_invoices.supplier_id` references `suppliers.id` with `restrictOnDelete`.
- `supplier_receipts.supplier_invoice_id` references `supplier_invoices.id` with `restrictOnDelete`.
- `supplier_payments.supplier_invoice_id` references `supplier_invoices.id` with `restrictOnDelete`.
- `supplier_payment_proof_attachments.supplier_payment_id` references `supplier_payments.id` with `restrictOnDelete`.

Decision:

- Do not change FK/delete semantics in this timestamp patch.
- Current restrict-on-delete behavior is preserved.

## 5. Current Proven Usage

Supplier/procurement root tables are used by:

- supplier invoice writers/readers
- supplier receipt writers/readers
- supplier payment writers/readers
- supplier invoice void flow
- supplier receipt reversal flow
- supplier payment reversal flow
- supplier payment proof attachment flow
- supplier payable reporting
- supplier invoice list/detail projections
- supplier list projections
- payable reminder readers
- seeders and load seeders
- reporting tests and procurement fixtures

Known high-risk semantics:

- `supplier_invoices.tanggal_pengiriman` is shipment/business date.
- `supplier_invoices.jatuh_tempo` is due/business date.
- `supplier_receipts.tanggal_terima` is receipt/business date.
- `supplier_payments.paid_at` is payment/business date.
- `created_at` and `updated_at` must remain system row timestamps only.

## 6. Recommended Schema Change

Create a new migration. Do not edit old migrations.

Recommended first patch:

- Add nullable-safe/backfilled `created_at` to `supplier_invoices`.
- Add nullable-safe/backfilled `updated_at` to `supplier_invoices`.
- Add nullable-safe/backfilled `created_at` to `supplier_receipts`.
- Add nullable-safe/backfilled `updated_at` to `supplier_receipts`.
- Add nullable-safe/backfilled `created_at` to `supplier_payments`.
- Add nullable-safe/backfilled `updated_at` to `supplier_payments`.

Preferred semantics:

- `created_at`: system row creation/persistence timestamp.
- `updated_at`: system row mutation timestamp.
- For insert-only rows, initial `updated_at` equals `created_at`.
- For update-capable writer paths, `updated_at` changes on update while `created_at` is preserved.

Do not add timestamp indexes in this slice.

Reason:

- Existing read paths use supplier id, invoice id, shipment date, due date, receipt date, paid date, proof status, lifecycle status, and projection joins.
- No proven read path currently filters/sorts supplier root rows by `created_at` or `updated_at`.
- Index hardening must follow real read-path proof.

## 7. Backfill Policy

Do not infer supplier root `created_at` from shipment date, due date, receipt date, payment date, proof upload date, void date, or revision order.

Safe policy:

- Keep timestamp columns nullable to preserve direct insert compatibility.
- Backfill existing rows with migration execution time only if the migration explicitly updates existing rows.
- Record that historical creation time for pre-patch rows remains approximate/unknown.
- Writers must set `created_at` and `updated_at` for new rows going forward.
- Update-capable writers must update `updated_at` without replacing `created_at`.

## 8. Domain And Report Impact

Expected domain impact:

- No change to supplier invoice domain semantics.
- No change to receipt domain semantics.
- No change to supplier payment semantics.
- No change to payable/outstanding math.
- No change to proof attachment semantics.
- No change to report period semantics.
- No change to reversal semantics.
- No change to void policy.

Forbidden impact:

- Do not use `created_at` or `updated_at` as supplier payable report date.
- Do not replace `tanggal_pengiriman`, `jatuh_tempo`, `tanggal_terima`, or `paid_at`.
- Do not change supplier invoice lifecycle semantics.
- Do not change receipt stock movement semantics.
- Do not change supplier payment proof semantics.
- Do not change reversal semantics.
- Do not change FK/delete semantics.
- Do not add timestamp indexes without proven read-path demand.

## 9. PostgreSQL Readiness Impact

Patch must avoid:

- relying on implicit MySQL timestamp defaults as domain truth;
- unsigned-only assumptions;
- JSON timestamp truth;
- DB engine-specific timestamp side effects;
- non-portable index claims.

Preferred:

- explicit application timestamp writes in supplier/procurement writers;
- nullable direct-insert compatibility until fixture migration is intentionally handled;
- no timestamp indexes without proven read-path demand.

## 10. Files To Touch In Patch Slice

Expected production files:

- new migration under `database/migrations/`
- `app/Adapters/Out/Procurement/DatabaseSupplierInvoiceWriterAdapter.php`
- `app/Adapters/Out/Procurement/DatabaseSupplierReceiptWriterAdapter.php`
- `app/Adapters/Out/Procurement/DatabaseSupplierPaymentWriterAdapter.php`

Expected test files:

- focused database schema test for supplier/procurement timestamp columns
- focused supplier invoice writer test
- focused supplier receipt writer test
- focused supplier payment writer test
- focused supplier payable/procurement reporting non-regression tests most likely to break

Docs:

- `docs/03_blueprints/db/0004_db_audit_matrix.md`
- this blueprint after proof

## 11. Files Not To Touch

Do not touch in this slice:

- supplier payable math
- supplier receipt stock movement logic
- supplier invoice lifecycle policy
- supplier invoice void policy
- supplier receipt reversal logic
- supplier payment reversal logic
- supplier payment proof attachment semantics
- inventory movement logic
- customer payment/refund/allocation math
- report period semantics
- UI
- API/mobile
- Go API
- PostgreSQL runtime implementation
- existing indexes unless a real read-path proof requires it
- FK/delete semantics

## 12. Characterization Proof Plan

Minimum RED proof should prove the current schema gap before implementation:

- `supplier_invoices` missing `created_at`.
- `supplier_invoices` missing `updated_at`.
- `supplier_receipts` missing `created_at`.
- `supplier_receipts` missing `updated_at`.
- `supplier_payments` missing `created_at`.
- `supplier_payments` missing `updated_at`.

Expected RED shape:

- Focused database schema test fails on missing supplier/procurement timestamp columns.
- Do not proceed to writer/schema patch until RED output is captured.

## 13. GREEN Proof Plan

Minimum proof after patch:

- `php -l` for changed PHP files.
- Targeted migration/database test for supplier/procurement timestamp columns.
- Targeted supplier invoice writer test proving new rows receive `created_at` and `updated_at`.
- Targeted supplier invoice writer update test proving `updated_at` changes while `created_at` is preserved.
- Targeted supplier receipt writer test proving new rows receive `created_at` and `updated_at`.
- Targeted supplier payment writer test proving new rows receive `created_at` and `updated_at`.
- Targeted supplier payment writer update test proving `updated_at` changes while `created_at` is preserved.
- Focused supplier payable reporting tests.
- Focused procurement invoice/receipt/payment tests most likely to break.
- `git diff --check`.

## 14. Rollback Or Defer Criteria

Stop or defer if:

- patch requires changing many unrelated fixtures manually;
- supplier payable math would change;
- receipt stock movement semantics would change;
- report date semantics would change;
- proof attachment semantics would change;
- reversal semantics would change;
- historical `created_at` would be falsely inferred from shipment/due/receipt/payment dates;
- direct insert compatibility cannot be preserved cleanly;
- writer timestamp behavior cannot be tested narrowly;
- FK/delete behavior becomes part of the change.

## 15. Current Decision

Patch is blueprint-approved only.

Decision:

- Add nullable `created_at` and `updated_at` to supplier/procurement root tables.
- Backfill pre-patch rows with migration execution time as approximate system timestamp.
- Writers must set explicit timestamps for new rows.
- Update-capable writers must update `updated_at` only on update paths.
- Do not add timestamp indexes.
- Do not change FK/delete semantics.
- Do not change business/report dates.
- Do not change supplier payable, receipt, payment, proof, void, reversal, inventory, or reporting semantics.

## 16. Focused Verification Result

Status after implementation:

- Pending.

Production files expected:

- pending

Test files expected:

- pending

Verification proof:

- pending

Remaining gaps:

- RED schema proof not yet captured.
- GREEN schema proof not yet captured.
- Writer timestamp proof not yet captured.
- Focused supplier/procurement/reporting baseline not yet captured.
- Full `make verify` not run.
- Browser/manual QA not run.
- PostgreSQL runtime migration not run.
