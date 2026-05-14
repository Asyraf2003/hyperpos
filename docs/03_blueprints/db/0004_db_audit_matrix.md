# DB Blueprint 0004 - DB Audit Matrix

Status: Active audit matrix  
Scope: P0 database hardening matrix for temporal audit, PostgreSQL readiness, CRUD/read path readiness  
Owner: HyperPOS  

## 1. Purpose

Dokumen ini menjadi matrix kerja untuk menyelesaikan masalah database hardening satu per satu.

Matrix ini mengikuti:

- `docs/03_blueprints/db/0001_temporal_audit_columns_blueprint.md`
- `docs/03_blueprints/db/0002_mysql_postgresql_crud_readiness_blueprint.md`
- `docs/03_blueprints/db/0003_db_hardening_workflow.md`
- `docs/02_architecture/adr/0028_mysql_to_postgresql_and_api_migration_readiness.md`

Patch schema tidak boleh dimulai hanya karena row sudah ada di matrix. Patch baru boleh dimulai setelah source proof, writer proof, read path proof, backfill policy, dan targeted proof plan cukup.

## 2. Status Legend

| Status | Meaning |
| --- | --- |
| Reported | Gap or target has been identified. |
| Audited | Source and current behavior have been inspected. |
| Patch Blueprinted | Narrow patch plan exists with scope and proof plan. |
| Characterized RED | Test or characterization proves current gap. |
| Patched Unverified | Source patch exists but proof is incomplete. |
| Targeted Verified | Targeted proof passes for the active gap. |
| Focused Verified | Focused blast-radius proof passes. |
| Docs Aligned | Docs reflect source, proof, and remaining gaps. |
| Deferred with owner acceptance | Scope is intentionally deferred with explicit acceptance. |

## 3. P0 Matrix

| Table group | Migration files | Category | Source-of-truth status | Business/effective date | Occurred/action date | System timestamps | Money/status/source columns | Actor/reason/audit link | Known read/write proof | PostgreSQL risk | CRUD/read path risk | Recommendation | Patch allowed now | Required proof before patch | Status |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `notes` | `database/migrations/2026_03_14_000100_create_notes_table.php`; `database/migrations/2026_04_22_000003_add_current_revision_pointer_to_notes_table.php`; `database/migrations/2026_04_27_000100_add_due_date_to_notes_table.php` | Transaction header table | P0 finance-sensitive root transaction header | FACT: `transaction_date`, `due_date` | FACT: `closed_at`, `reopened_at` | GAP: no `created_at`, no `updated_at` proven in current migration output | FACT: `total_rupiah`, `note_state`, `current_revision_id`, `latest_revision_number` | FACT: `closed_by_actor_id`, `reopened_by_actor_id`; GAP: reason/audit linkage not fully verified in this slice | FACT: `DatabaseNoteWriterAdapter` inserts and updates without `created_at`/`updated_at`; FACT: many tests and seeders direct insert/updateOrInsert `notes` | Medium: timestamp defaults/backfill must stay portable and not rely on MySQL-only behavior | Medium: root table feeds payment, refund, revision, reporting, dashboard, due reminder, and exports | Create narrow patch blueprint for `notes` timestamp semantics before schema patch | No | Full direct insert inventory, writer/update policy, backfill policy, RED/characterization test, focused blast-radius test list | Audited |
| `customer_payments` | `database/migrations/2026_03_14_000600_create_customer_payments_table.php`; `database/migrations/2026_04_27_000700_add_payment_method_and_cash_details_to_customer_payments.php` | Payment/source financial table | P0 payment source | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit after `notes` | No | Migration, writer, allocation linkage, reporting path, direct fixtures | Reported |
| `customer_refunds` | `database/migrations/2026_03_15_000100_create_customer_refunds_table.php` | Refund/source financial table | P0 refund source | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit after payment/refund root dependency is clear | No | Migration, writer, refund allocation linkage, reporting path, direct fixtures | Reported |
| `payment_allocations` | `database/migrations/2026_03_14_000700_create_payment_allocations_table.php` | Allocation table | P0 allocation truth | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit after `customer_payments` | No | Parent payment timestamp inheritance, immutability, writer, over-allocation tests | Reported |
| `payment_component_allocations` | `database/migrations/2026_04_02_000800_create_payment_component_allocations_table.php` | Allocation table | P0 component allocation truth | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit with allocation group | No | Parent payment/refund source, component semantics, writer, selected-row payment tests | Reported |
| `refund_component_allocations` | `database/migrations/2026_04_02_000900_create_refund_component_allocations_table.php` | Allocation table | P0 refund component allocation truth | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit with allocation group | No | Parent refund source, component semantics, writer, refund reporting tests | Reported |
| `supplier_invoices` | `database/migrations/2026_03_12_000200_create_supplier_invoices_table.php` | Procurement transaction header | P0 supplier payable/source header | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit after customer note/payment root group unless owner reprioritizes procurement | No | Migration, writer, receipt/payment linkage, void policy, reporting path | Reported |
| `supplier_receipts` | `database/migrations/2026_03_12_000400_create_supplier_receipts_table.php` | Procurement receipt header | P0 stock/procurement receipt source | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit with supplier group | No | Migration, writer, stock movement linkage, receipt reversal path | Reported |
| `supplier_payments` | `database/migrations/2026_03_12_000800_create_supplier_payments_table.php` | Supplier payment source | P0 supplier payable payment source | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit with supplier payment group | No | Migration, writer, proof attachment linkage, reversal path, report path | Reported |
| `inventory_movements` | `database/migrations/2026_03_12_000600_create_inventory_movements_table.php` | Ledger/movement table | P0 stock movement source | GAP | GAP | GAP | GAP | GAP | GAP | Medium | Medium | Audit after transaction roots and supplier receipt linkage are mapped | No | Migration, writer, source id, reversal behavior, stock/report path | Reported |

## 4. Active Slice

Current active table group:

- `notes`

Current proven problem:

- `notes` is the root transaction header.
- `notes` has business date and lifecycle action timestamps.
- `notes` does not have proven system row timestamps.
- Current writer creates and updates `notes` without `created_at` or `updated_at`.
- Many tests and seeders direct insert into `notes`, so a naive non-null timestamp patch can break fixtures.

## 5. Current Decision

Do not patch schema yet.

Next safe step:

- Create a narrow patch blueprint for `notes`.
- Define whether the first patch should add only `created_at`, or both `created_at` and `updated_at`.
- Define deterministic backfill policy without inventing historical creation time.
- Define RED/characterization test and focused blast-radius tests before implementation.

