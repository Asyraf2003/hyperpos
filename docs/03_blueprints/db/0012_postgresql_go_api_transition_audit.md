# PostgreSQL and Go API Transition Audit

## Status

This document captures the current migration-readiness conclusion for a future PostgreSQL transition and Go API extraction.

This is an audit and transition reference, not an implementation completion claim.

## Source Evidence

The current conclusion is based on these repository observations:

- Laravel tree shows a hexagonal-style structure:
  - `app/Core`
  - `app/Application`
  - `app/Ports`
  - `app/Adapters/In`
  - `app/Adapters/Out`
  - domain-specific providers
- Database migrations show:
  - many domain IDs stored as string primary keys
  - some Laravel/system IDs stored as auto-increment IDs
  - some employee/payroll/debt IDs stored as UUID columns
  - integer or big integer money fields
  - audit, versioning, settlement, projection, and read-model tables
- Raw SQL scan shows high-risk PostgreSQL compatibility candidates in:
  - reporting queries
  - note history queries
  - procurement projection and policy SQL fragments
  - payroll reporting date casting
  - push notification writer `updateOrInsert`
  - lock-based mutation readers/writers
- Migration scan shows PostgreSQL-relevant compatibility risks:
  - `unsigned*` columns
  - `dateTime` and `timestamp` mix
  - `longText` JSON-like payload storage
  - `mediumText`
  - MySQL-style migration alteration helpers such as `->after()` and `->change()`
- JSON inventory shows mixed storage:
  - native JSON columns for version snapshots and audit payloads
  - long text columns for some JSON payload blobs
- Money and quantity inventory shows integer-based financial storage, which is good, but signedness and non-negative invariants still need PostgreSQL-specific enforcement or tests.

## Executive Conclusion

The project is structurally ready for a staged PostgreSQL and Go API transition, but it is not ready for a direct database cutover.

Recommended readiness rating:

- Go API transition readiness: 8.0 / 10
- PostgreSQL transition readiness: 6.4 / 10
- Read-only API extraction readiness: 8.0 / 10
- Write mutation migration readiness: 5.5 / 10

The safest path is:

1. PostgreSQL compatibility harness first.
2. Laravel test suite on PostgreSQL second.
3. Read-only Go API over projection/read-model tables third.
4. Write migration only after parity, transaction, lock, idempotency, and audit tests exist.

Do not start with a broad Go rewrite.

## Key Architectural Strengths

### 1. Hexagonal boundary is already useful

The current structure separates domain, application use cases, ports, and adapters. This makes the future Go API transition easier because Laravel is not acting as the only place where domain concepts exist.

Expected transition shape:

- Laravel remains the existing web/admin shell.
- Laravel remains the write owner for sensitive mutations during the first transition phase.
- Go starts as a read-only API service.
- Go reads PostgreSQL projection/read-model tables first.
- Write ownership moves only per bounded context after proof.

### 2. Projection/read models already exist

Existing projection tables are strong candidates for Go read APIs:

- `note_history_projection`
- `supplier_invoice_list_projection`
- `supplier_list_projection`
- inventory/reporting source queries
- dashboard/reporting query outputs

These are better first targets than normalized transactional tables because they reduce business-rule duplication in Go.

### 3. Integer rupiah storage is migration-friendly

Money values are stored as integer or big integer rupiah fields. This is correct for PHP, Go, MySQL, and PostgreSQL interoperability.

This avoids floating point drift and simplifies Go DTO mapping.

### 4. Audit and versioning are mature enough for migration safety

The schema includes audit/versioning foundations such as:

- `audit_events`
- `audit_event_snapshots`
- product versions
- supplier versions
- employee versions
- supplier invoice versions
- note revisions
- note revision lines
- note revision settlements
- note revision surplus dispositions
- note revision surplus refund payments

This is valuable for reconciliation, dual-read comparison, and rollback analysis during a staged migration.

## PostgreSQL Risks

### 1. Mixed ID strategy

The schema currently uses several ID styles:

- string primary keys for many domain tables
- UUID primary keys for employee/payroll/debt-related tables
- auto-increment IDs for Laravel/system tables such as users, tokens, jobs, and push subscriptions
- domain-key primary keys for projection tables

This is acceptable, but Go must treat IDs through explicit type contracts.

Recommended Go boundary policy:

- Domain IDs should be exposed as opaque strings in API contracts.
- Laravel/system IDs may remain numeric where they are truly system-owned.
- UUID-backed tables can be represented as strings at API boundaries unless internal Go code needs strict UUID validation.
- Do not normalize all historical IDs just for aesthetic consistency.

### 2. Unsigned integer semantics

PostgreSQL does not have native unsigned integer types.

Migration inventory shows multiple unsigned fields, including revision numbers, line counts, counters, file size, and some money/subtotal fields.

Recommended PostgreSQL policy:

- Keep integer/bigint storage.
- Add explicit check constraints for non-negative fields where the database must protect invariants.
- At minimum, add PostgreSQL test assertions for non-negative critical fields.
- Do not rely on Laravel `unsigned*` semantics as a PostgreSQL guarantee.

Priority fields for non-negative checks:

- `revision_no`
- `revision_number`
- `latest_revision_number`
- `line_no`
- `line_count`
- `payment_count`
- `receipt_count`
- `proof_attachment_count`
- `invoice_count`
- `invoice_unpaid_count`
- `file_size_bytes`
- `grand_total_rupiah`
- `subtotal_rupiah`
- `allocated_amount_rupiah`
- `refunded_amount_rupiah`

### 3. Timestamp and timezone policy

The migrations contain a mix of:

- `dateTime`
- `timestamp`
- `timestamps`
- nullable timestamps
- manual operational timestamps

For PostgreSQL and Go API behavior, timestamp semantics must be frozen.

Recommended decision:

- API boundary timestamps must be UTC ISO-8601.
- New PostgreSQL-facing event/audit timestamps should prefer explicit UTC semantics.
- Existing timestamp columns should not be mass-changed without migration tests and data compatibility checks.
- Reporting date filters should be tested for date boundary behavior.

High-priority date/time areas:

- audit `occurred_at`
- note mutation `occurred_at`
- note closed/reopened timestamps
- supplier invoice void/superseded timestamps
- payment/refund operational timestamps
- payroll disbursement date filtering
- dashboard/reporting date grouping

### 4. JSON storage classification

The schema uses both native JSON columns and long text JSON-like payload fields.

Recommended classification:

- Use JSON/JSONB for payloads that will be queried, indexed, or structurally validated.
- Keep text only for opaque historical snapshots where no database query is required.
- For Go interoperability, define canonical JSON encoding expectations for audit/snapshot payloads.
- Avoid comparing raw JSON strings across PHP and Go unless canonicalization is defined.

Priority JSON fields:

- version `snapshot_json`
- audit `metadata_json`
- audit snapshot `payload_json`
- note revision line `payload`
- transaction workspace draft `payload_json`
- note mutation snapshot `payload_json`

### 5. MySQL-oriented migration helpers

The migration scan includes helpers such as:

- `->after()`
- `->change()`
- `unsigned*`
- `mediumText`
- `longText`
- `dateTime`

These do not automatically block PostgreSQL, but they must be tested under a PostgreSQL migration run.

Important correction for future scans:

    rg -n -- "->after\\(|->change\\(|unsigned|mediumText|longText|dateTime\\(" database/migrations

The `--` is required because the pattern begins with `-`.

### 6. Raw SQL portability

Raw SQL is concentrated mostly in adapters, which is good. However, PostgreSQL parity must be proven for:

- `DATE(...)`
- `CONCAT(...)`
- `TRIM(BOTH ... FROM ...)`
- `GREATEST(...)`
- `LEAST(...)`
- window functions
- `lockForUpdate()`
- `updateOrInsert()`
- `insertGetId()`
- `insertOrIgnore()`
- raw aggregation queries
- projection rebuild queries

Most of these are potentially PostgreSQL-compatible, but compatibility is not the same as parity.

## Go API Transition Strategy

### Recommended first Go API targets

Start with read-only endpoints backed by projection/read-model tables.

Good first candidates:

1. Product search/read
2. Supplier list/read
3. Supplier invoice list/detail
4. Note history read
5. Dashboard/reporting read-only endpoints
6. Mobile read-only endpoints
7. Attachment metadata read

These endpoints minimize write-risk and avoid duplicating critical financial mutation logic too early.

### Forbidden early Go migration targets

Do not move these to Go first:

1. Customer payment allocation
2. Customer refunds
3. Refund component allocations
4. Note revisions
5. Note revision settlement
6. Note surplus disposition/refund paid
7. Inventory movement and costing projection
8. Supplier receipt/payment reversal
9. Payroll/debt reversal

Reason:

These areas depend on transaction boundaries, lock behavior, idempotency, audit trails, financial invariants, and projection correctness. They require golden parity tests before ownership can move.

## Required Proof Before PostgreSQL Cutover

A PostgreSQL cutover must not be considered safe until these pass:

1. Full migration fresh run on PostgreSQL.
2. Seed or fixture load on PostgreSQL.
3. Architecture tests.
4. Unit tests.
5. Feature tests for sensitive workflows.
6. Reporting/export tests.
7. Projection rebuild tests.
8. Query parity tests for high-risk raw SQL.
9. Transaction/lock behavior tests.
10. Timezone/date-boundary tests.
11. JSON encoding/decoding tests.
12. Constraint tests for non-negative financial and counter fields.

## Required Proof Before Go API Production Use

A Go API endpoint should not be published until these exist:

1. Frozen JSON response contract.
2. Laravel response fixture for the same scenario.
3. Go response fixture for the same scenario.
4. Diff/parity check between Laravel and Go responses.
5. Pagination/filter/sort parity.
6. Error envelope contract.
7. Auth/access boundary contract.
8. Observability/log redaction rules.
9. Backward-compatible rollout plan.

## High-Risk File Areas

Prioritize compatibility review for:

- `app/Adapters/Out/Reporting/Queries`
- `app/Adapters/Out/Note/Queries`
- `app/Adapters/Out/Procurement/Concerns/ProcurementInvoicePolicySqlFragments.php`
- `app/Adapters/Out/Procurement/DatabaseProcurementInvoiceTableReaderAdapter.php`
- `app/Adapters/Out/EmployeeFinance/DatabasePayrollReportingSourceReaderAdapter.php`
- `app/Adapters/Out/PushNotification/DatabasePushSubscriptionWriterAdapter.php`
- lock-based adapters in Note, Procurement, and Inventory
- projection writer adapters using `updateOrInsert`

## Suggested Transition Phases

### Phase 0: Audit lock

No behavior change.

Deliverables:

- PostgreSQL compatibility matrix
- high-risk query inventory
- migration portability inventory
- ID mapping policy
- timestamp policy
- JSON storage policy

### Phase 1: PostgreSQL test harness

Deliverables:

- PostgreSQL test database setup
- migration fresh proof
- seed/fixture proof
- test suite proof
- high-risk query proof

### Phase 2: Read-model contract freeze

Deliverables:

- API response schemas
- pagination/filter/sort rules
- error envelope
- datetime format
- ID type policy
- money representation policy

### Phase 3: Go read-only API

Deliverables:

- Go service reads projection tables only
- no write mutation ownership
- Laravel vs Go parity tests
- shadow-read comparison

### Phase 4: Controlled production read rollout

Deliverables:

- route-level rollout
- logs and metrics
- fallback to Laravel
- response diff monitoring where possible

### Phase 5: Write migration evaluation

Only after read-only Go API is stable.

Write migration candidates must be selected per bounded context, not per technical convenience.

## Current Decision

The project is ready for PostgreSQL and Go migration planning.

The project is not ready for direct PostgreSQL production cutover.

The project is ready for a Go read-only API spike over projection/read-model tables.

The project is not ready for Go ownership of financial, stock, refund, note revision, supplier reversal, or payroll/debt mutation logic.

## Immediate Next CLI

Run from repository root.

### 1. Fixed migration portability scan

    rg -n -- "->after\\(|->change\\(|unsigned|mediumText|longText|dateTime\\(" database/migrations

### 2. PostgreSQL high-risk query scan

    rg -n "whereRaw|orderByRaw|havingRaw|selectRaw|DB::raw|DATE\\(|CONCAT\\(|TRIM\\(|GREATEST\\(|LEAST\\(|lockForUpdate|updateOrInsert|insertGetId|insertOrIgnore" app/Adapters/Out app/Application --glob '*.php'

### 3. ID type inventory

    rg -n "\\$table->(string|uuid|id|foreignUuid|foreignId)\\(" database/migrations

### 4. JSON storage inventory

    rg -n "\\$table->(json|longText|mediumText|text)\\('.*(json|payload|metadata|snapshot)" database/migrations

### 5. Money and quantity inventory

    rg -n "\\$table->(integer|bigInteger|unsignedInteger|unsignedBigInteger)\\('.*(rupiah|amount|qty|total|price|cost|balance|count|revision)" database/migrations

### 6. First PostgreSQL target review list

    printf '%s\n' \
      "app/Adapters/Out/Reporting/Queries" \
      "app/Adapters/Out/Note/Queries" \
      "app/Adapters/Out/Procurement/Concerns/ProcurementInvoicePolicySqlFragments.php" \
      "app/Adapters/Out/Procurement/DatabaseProcurementInvoiceTableReaderAdapter.php" \
      "app/Adapters/Out/EmployeeFinance/DatabasePayrollReportingSourceReaderAdapter.php" \
      "app/Adapters/Out/PushNotification/DatabasePushSubscriptionWriterAdapter.php"

## Definition of Done for This Audit Slice

This audit slice is complete when:

- this document is committed;
- the fixed scans run without CLI syntax error;
- the resulting PostgreSQL compatibility risks are triaged into:
  - compatible
  - needs PostgreSQL test
  - needs migration patch
  - needs query rewrite
  - needs architecture decision
- no production cutover is claimed without PostgreSQL test proof.

