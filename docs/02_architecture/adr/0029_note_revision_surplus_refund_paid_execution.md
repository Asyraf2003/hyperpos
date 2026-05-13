# ADR 0029 - Note Revision Surplus Refund Paid Execution

## Status

Accepted by owner for implementation planning.

This ADR is a contract decision before production implementation.

No production code, migration, UI, report query, export, or API patch is authorized until this ADR is reviewed and accepted in the working branch.

## Context

HyperPOS supports paid note revision.

A downward paid revision can create surplus.

ADR 0026 defines the two-step surplus model:

- revision settlement creates overpaid_pending
- overpaid_pending is not revenue
- overpaid_pending is not automatic refund paid
- overpaid_pending is not automatic customer credit
- explicit disposition later converts pending surplus into an operational state
- refund_due is a liability decision
- refund_paid is an execution state

ADR 0027 defines the first surplus disposition table:

    note_revision_surplus_dispositions

The first implemented disposition type is:

    refund_due

ADR 0028 defines MySQL-to-PostgreSQL and API migration-readiness constraints for finance-sensitive schema and application contracts.

The current source audit found that existing refund infrastructure is payment-note/component based:

- customer_refunds stores customer_payment_id, note_id, amount_rupiah, refunded_at, and reason
- CustomerRefund requires customer_payment_id
- RecordCustomerRefundOperation validates refund against customer_payment_id and note_id allocated/refunded totals
- RecordCustomerRefundOperation allocates refund across payment components
- RecordCustomerRefundTransaction can trigger note refunded lifecycle
- RecordCustomerRefundTransaction can trigger inventory reversal
- RecordCustomerRefundTransaction writes legacy audit_logs event customer_refund_recorded
- refund_due implementation writes canonical audit_events and audit_event_snapshots

The owner approved a separate refund_paid execution ledger for refund_due surplus.

## Problem

refund_paid from refund_due cannot safely reuse customer_refunds or RecordCustomerRefundOperation.

The reasons are structural, not cosmetic.

First, refund_due surplus does not prove a single valid customer_payment_id.

note_revision_settlements stores carry-forward and surplus amounts, not a stable source customer_payment_id attribution.

Second, refund_paid from surplus is not a component refund.

It is money leaving the business because a previous surplus disposition became payable to the customer.

It should not create refund_component_allocations.

Third, existing customer refund transaction has side effects that are wrong for surplus refund_paid:

- it may trigger note refunded lifecycle
- it may trigger inventory reversal
- it writes legacy audit_logs instead of canonical audit_events
- it is coupled to payment allocations

Fourth, putting surplus refund_paid into customer_refunds would make reports and cash ledger easy at first, but would contaminate the meaning of customer_refunds with two different domains:

- component/payment refund
- surplus disposition money-out

That would make future reporting, reversal, PostgreSQL migration, and API contracts fragile.

Fifth, a bridge table between customer_refunds and note_revision_surplus_dispositions improves traceability but does not solve the fake customer_payment_id problem.

It also creates double-write and double-counting risk.

## Decision

refund_paid from refund_due must use a new canonical execution table:

    note_revision_surplus_refund_payments

This table records actual money-out execution for surplus refund_due.

This table is separate from:

    customer_refunds

customer_refunds remains the legacy/current table for customer payment refund flow that is tied to payment allocation and component refund behavior.

note_revision_surplus_dispositions remains the liability/disposition decision table.

note_revision_surplus_refund_payments becomes the cash-out execution ledger for surplus refund_due.

## Rejected Storage Options

### Rejected: extend customer_refunds

Rejected because customer_refunds requires customer_payment_id and is coupled to existing payment-note refund logic.

The surplus refund_paid execution path must not invent a customer_payment_id.

The surplus refund_paid execution path must not create component allocations.

The surplus refund_paid execution path must not trigger note refunded lifecycle.

The surplus refund_paid execution path must not trigger inventory reversal.

### Rejected: bridge table between note_revision_surplus_dispositions and customer_refunds

Rejected for the first implementation slice.

A bridge table improves traceability, but it still requires a customer_refunds row.

That keeps the fake customer_payment_id problem alive.

It also creates double-write and double-counting risk between the bridge and customer_refunds.

The useful part of the bridge design is kept by storing explicit source ids directly inside note_revision_surplus_refund_payments.

## Positive Properties Preserved From Rejected Options

The useful part of extending customer_refunds is that cash-out rows become visible in reports and cash ledger.

This ADR keeps that benefit by requiring explicit report and cash ledger readers for note_revision_surplus_refund_payments.

The useful part of a bridge table is source traceability.

This ADR keeps that benefit by storing direct source links:

- note_revision_surplus_disposition_id
- note_revision_settlement_id
- note_root_id
- note_revision_id
- audit_event_id

## Table Contract

The table name is:

    note_revision_surplus_refund_payments

Required columns:

    id
    note_revision_surplus_disposition_id
    note_revision_settlement_id
    note_root_id
    note_revision_id
    amount_rupiah
    effective_date
    occurred_at
    status
    idempotency_key
    audit_event_id
    created_at
    updated_at

Column meanings:

    id
        String primary identity for the refund payment row.

    note_revision_surplus_disposition_id
        Source refund_due disposition being executed.

    note_revision_settlement_id
        Source settlement snapshot that created the surplus.

    note_root_id
        Root note id for fast note detail, timeline, report, and API lookup.

    note_revision_id
        Source revision id related to the surplus settlement.

    amount_rupiah
        Positive integer rupiah amount actually paid out.

    effective_date
        Business cash date when the money leaves the business.
        Used by cash ledger, report, and export.

    occurred_at
        Domain action timestamp.
        Used by audit, timeline, and ordering of actions.

    status
        Lifecycle status of this execution row.
        First implementation supports active only.
        Future statuses may include reversed or canceled only after a separate reversal/cancel ADR.

    idempotency_key
        Transport/application idempotency key for repeated submit protection.

    audit_event_id
        Canonical audit_events row id for this mutation.

    created_at
        Row creation timestamp.

    updated_at
        Row update timestamp.
        Nullable in the first slice if rows are immutable after create.

## Required Indexes And Constraints

Required primary key:

    primary(id)

Required unique constraints:

    unique(audit_event_id)
    unique(note_revision_surplus_disposition_id, idempotency_key)

Required indexes:

    index(note_revision_surplus_disposition_id, status)
    index(note_root_id, status)
    index(note_root_id, occurred_at)
    index(note_root_id, effective_date)
    index(effective_date, status)
    index(status, effective_date)
    index(note_revision_settlement_id)
    index(note_revision_id)

Required foreign keys:

    note_revision_surplus_disposition_id references note_revision_surplus_dispositions(id) restrict on delete
    note_revision_settlement_id references note_revision_settlements(id) restrict on delete
    note_root_id references notes(id) restrict on delete
    note_revision_id references note_revisions(id) restrict on delete
    audit_event_id references audit_events(id) restrict on delete

Cascade delete is rejected for all financial history in this table.

## Performance Contract

This table must support read, create, and future edit/reversal paths under one second for the expected HyperPOS operational scale.

The first implementation must not rely on table scans for critical paths.

Required fast read paths:

1. Note detail surplus execution timeline

    lookup by note_root_id and occurred_at

2. Refund_due execution page

    lookup by note_revision_surplus_disposition_id and status

3. Idempotency repeated submit

    lookup by note_revision_surplus_disposition_id and idempotency_key

4. Transaction cash ledger

    lookup by effective_date and status

5. Transaction report and export

    lookup by effective_date and status

6. Audit timeline

    lookup by note_root_id and occurred_at, then audit_event_id when needed

7. Future API

    lookup by id, note_root_id, note_revision_surplus_disposition_id, status, and effective_date

The report/export layer must reuse the same read model or query semantics as the UI/report dataset.

Export queries must not invent separate financial calculations.

## PostgreSQL Readiness Contract

The current implementation remains MySQL.

PostgreSQL implementation is out of scope.

However, the schema must be PostgreSQL-ready.

Rules:

- use string ids for domain identity
- do not use auto-increment ids as public identity
- use string status, not MySQL enum
- use integer or big integer rupiah
- do not use float or decimal for rupiah totals
- do not rely on MySQL unsigned behavior as the only negative guard
- use domain/application validation for positive money
- use explicit effective_date for business cash date
- use explicit occurred_at for domain action timestamp
- do not infer business date from created_at
- do not infer audit timestamp from effective_date
- do not use JSON as source of truth for money, status, source ids, actor, business date, or occurred_at
- use restrict-on-delete for financial history
- do not use nullable foreign keys as a shortcut for missing source snapshots
- keep indexes aligned with actual UI, report, export, audit, and API read paths

## Domain Invariants

A surplus refund payment is valid only if:

- source refund_due disposition exists
- source disposition type is refund_due
- source disposition status is active
- amount_rupiah is greater than zero
- amount_rupiah is not greater than remaining unpaid refund_due amount
- effective_date is present and valid
- occurred_at is present and valid
- actor id is present through command or audit event
- actor role is present through command or audit event
- first slice actor role is admin only
- reason is present through command or audit event
- idempotency_key is present
- audit_event_id is present
- mutation runs inside one transaction
- source disposition is locked before remaining amount is computed

The following are invalid:

- customer_payment_id requirement
- refund_component_allocations creation
- note refunded lifecycle trigger
- inventory reversal trigger
- customer_credit creation
- customer_balance_entries creation
- PostgreSQL implementation
- Go API implementation

## Remaining Amount Rule

The remaining refund_due amount is computed as:

    active refund_due disposition amount
    minus active surplus refund payments for that disposition

The remaining amount must never go negative.

Repeated payments are allowed only while remaining refund_due amount is greater than zero.

Partial refund_paid is allowed if the amount is less than remaining refund_due.

Full refund_paid is reached when remaining refund_due amount becomes zero.

The first slice does not need to mutate note_revision_surplus_dispositions status to paid.

If later UX needs a paid/closed status on refund_due, that must be added through an explicit status transition contract.

## Idempotency Rule

The command must include idempotency_key or a request id promoted into idempotency_key.

The table must enforce:

    unique(note_revision_surplus_disposition_id, idempotency_key)

Behavior:

- same disposition plus same idempotency_key plus same payload returns existing success
- same disposition plus same idempotency_key plus different payload is rejected
- different idempotency_key is allowed only if remaining refund_due amount is sufficient
- database unique constraint is required as a final guard against repeated submit races

Idempotency must be checked inside the same transaction as the mutation.

## Concurrency Rule

Two admins must not be able to overpay the same refund_due.

The use case must:

1. begin transaction
2. load source refund_due disposition by id with row lock
3. validate disposition type, status, and source ids
4. load or check existing idempotency row
5. sum active refund payments for the locked disposition
6. compute remaining refund_due amount
7. reject amount greater than remaining
8. create canonical audit event
9. create surplus refund payment row
10. commit transaction

The lock target is the source refund_due disposition row.

The payment rows alone are not enough as the only concurrency control, because two transactions can otherwise read the same remaining amount before either insert is visible.

## Audit Event Contract

The mutation must write canonical audit_events and audit_event_snapshots.

Legacy audit_logs is not the canonical audit for this flow.

Event name:

    note_revision_surplus_refund_paid_recorded

Audit event fields:

    bounded_context: note
    aggregate_type: note_revision_surplus_refund_payment
    aggregate_id: note_revision_surplus_refund_payment id
    event_name: note_revision_surplus_refund_paid_recorded
    actor_id: admin actor id
    actor_role: admin
    reason: required reason
    source_channel: transport/source channel when provided
    request_id: request id when provided
    correlation_id: correlation id when provided
    occurred_at: domain action timestamp
    metadata_json: source ids and amount

Required metadata:

    note_root_id
    note_revision_id
    note_revision_settlement_id
    note_revision_surplus_disposition_id
    note_revision_surplus_refund_payment_id
    amount_rupiah
    effective_date
    disposition_type
    idempotency_key

Required before snapshot:

    refund_due_rupiah
    active_refund_paid_rupiah
    remaining_refund_due_rupiah

Required after snapshot:

    refund_due_rupiah
    active_refund_paid_rupiah
    remaining_refund_due_rupiah

The audit event and refund payment row must be written in the same transaction.

## Reporting Contract

Transaction reporting must explicitly distinguish:

- refunded_rupiah from customer_refunds
- refund_due_rupiah from note_revision_surplus_dispositions
- surplus_refund_paid_rupiah from note_revision_surplus_refund_payments
- remaining_refund_due_rupiah as refund_due minus surplus_refund_paid

refund_due must not be counted as cash out.

surplus_refund_paid must be counted as cash out.

customer_refunds and note_revision_surplus_refund_payments must not be merged into one undifferentiated refunded_rupiah field without explicit source labels.

If a summary field needs total money out, it must document whether it includes:

- customer refund cash out
- surplus refund paid cash out
- both

## Cash Ledger Contract

The transaction cash ledger must include surplus refund payments as a separate outflow source.

Required row semantics:

    source_table: note_revision_surplus_refund_payments
    event_type: surplus_refund_paid
    direction: out
    event_date: effective_date
    event_amount_rupiah: amount_rupiah
    note_id: note_root_id
    source_id: note_revision_surplus_refund_payment id
    source_disposition_id: note_revision_surplus_disposition_id

Existing customer_refunds rows remain event_type refund or existing compatible naming.

The cash ledger must not count refund_due as outflow.

## Export Contract

Excel and PDF exports must follow the reporting dataset.

Exports must not run separate financial calculations that can diverge from the page/report dataset.

Required export visibility after implementation:

- refund_due_rupiah
- surplus_refund_paid_rupiah
- remaining_refund_due_rupiah

The exact column labels can be decided in the UI/export slice, but the dataset contract must be explicit before UI polish.

## Audit Timeline Contract

The note detail audit timeline must be able to display:

- refund_due created
- refund_paid recorded

For refund_paid recorded, the timeline should show:

- amount
- remaining before
- remaining after
- effective_date
- occurred_at
- actor role
- reason

The timeline must read canonical audit_events and audit_event_snapshots.

## UI Contract

UI may submit refund_paid only through backend use case.

UI must not compute financial truth.

UI may display backend-provided remaining amount.

UI may clamp input for usability, but backend remains the source of truth.

Fallback form submit must remain valid if JavaScript fails.

Repeated submit must be protected by backend idempotency, not JavaScript only.

## API Readiness Contract

Go API implementation is out of scope.

However, the use case must be transport-independent.

Blade controller and future API must call the same application use case.

The application use case must own:

- idempotency
- source validation
- amount validation
- concurrency lock orchestration
- audit event creation
- refund payment creation

Controllers must only map request/session/user context into command input and map result into response.

## Lifecycle Boundaries

refund_paid from surplus must not:

- mark note refunded
- cancel note
- revise note
- reverse inventory
- create stock movement
- create refund_component_allocations
- create customer_credit
- create customer_balance_entries
- mutate customer_refunds
- mutate note_revision_settlements
- silently reclaim money in later revision

Money already refund_paid has left the business.

Later note revision must not silently consume it.

Any reversal of refund_paid requires a future explicit reversal/cancel contract.

## Reversal And Edit Policy

The first implementation slice creates active refund payment rows only.

Financial row edit is not allowed as a casual update.

Future edit/reversal must be explicit.

The schema keeps status and updated_at so a future reversal/cancel flow can be added without redesign.

However, the first slice must not expose arbitrary edit mutation.

Allowed future direction:

- reversal/cancel use case
- explicit reason
- actor id and role
- canonical audit event
- before/after snapshots
- no destructive delete
- no hidden amount rewrite

Rejected future direction:

- editing amount_rupiah in place without audit
- deleting payment row
- rewriting effective_date silently
- changing status without audit event
- hiding mistakes by mutating history

## Expected Use Case Shape

Future use case name may be adjusted after implementation planning.

Recommended shape:

    RecordNoteRevisionSurplusRefundPayment

Input:

    note_revision_surplus_disposition_id
    amount_rupiah
    effective_date
    reason
    actor_id
    actor_role
    occurred_at
    source_channel
    request_id
    correlation_id
    idempotency_key

Output:

    refund_payment_id
    note_revision_surplus_disposition_id
    note_revision_settlement_id
    note_root_id
    note_revision_id
    amount_rupiah
    effective_date
    occurred_at
    status
    remaining_refund_due_rupiah

Required behavior:

- require admin actor
- require reason
- require idempotency key
- lock source refund_due disposition
- reject invalid source
- reject invalid amount
- reject amount greater than remaining
- write canonical audit event
- write refund payment row
- return stable result DTO

## Test Plan For Implementation Slice

Minimum RED or source-gap proof:

- existing source cannot record refund_paid from refund_due without fake customer_payment_id or wrong lifecycle

Targeted tests:

- records surplus refund payment from active refund_due
- rejects blank reason
- rejects non-admin actor
- rejects amount greater than remaining refund_due
- rejects invalid disposition id
- rejects non-refund_due source if such fixture exists
- rejects repeated idempotency key with different payload
- returns existing success for repeated idempotency key with same payload
- supports partial payment and computes remaining
- supports full payment and remaining zero
- does not write customer_refunds
- does not write refund_component_allocations
- does not mark note refunded
- does not reverse inventory
- writes audit_events and audit_event_snapshots

Focused blast-radius tests:

- refund_due creation still passes
- note detail surplus payload still shows refund_due
- transaction report distinguishes refund_due and surplus_refund_paid
- cash ledger includes surplus_refund_paid only after actual payment
- Excel/PDF export parity follows report dataset
- existing customer refund flow still passes
- existing inventory reversal tests still pass
- existing note refunded lifecycle tests still pass

Performance-oriented tests or checks:

- query paths use indexed columns
- no broad unfiltered scan for note detail timeline
- no export query divergence from report dataset
- no N+1 query pattern in note detail payment timeline

## Migration Stop Conditions

Stop before migration patch if:

- source disposition lock path is unclear
- idempotency lookup cannot be enforced with unique constraint
- audit event cannot be written in the same transaction
- report/cash ledger source naming is not decided
- FK order conflicts with existing migrations
- implementation would require fake customer_payment_id
- implementation would mutate customer_refunds
- implementation would make UI the financial truth
- implementation would require customer_credit or customer_balance_entries
- implementation would require PostgreSQL or Go API work

## Consequences

Positive consequences:

- refund_paid from surplus has a clean execution ledger
- customer_refunds remains stable for payment/component refund
- no fake customer_payment_id
- no accidental note refunded lifecycle
- no accidental inventory reversal
- audit_events remains canonical
- cash ledger can include actual money-out only
- refund_due remains visible as liability until paid
- PostgreSQL migration remains protected
- future API can use the same application contract
- future reversal/cancel can be added without deleting financial history

Costs:

- new migration is required
- new ports, DTOs, adapter, use case, and tests are required
- reporting and exports must add explicit source handling
- cash ledger must union another outflow source
- audit timeline must support another event type
- first implementation is more deliberate than reusing customer_refunds

## Final Decision

Use note_revision_surplus_refund_payments for refund_paid execution from refund_due.

Do not extend customer_refunds for this first slice.

Do not create a bridge table to customer_refunds for this first slice.

Do not require customer_payment_id.

Do not create refund_component_allocations.

Do not trigger note refunded lifecycle.

Do not trigger inventory reversal.

Do not implement customer_credit.

Do not implement customer_balance_entries.

Do not implement PostgreSQL.

Do not implement Go API.

## Next Safe Step

Create this ADR file.

After ADR is accepted in repo, implement the first backend slice:

1. migration
2. DTO/domain result objects
3. ports
4. database adapter
5. use case with transaction, idempotency, lock, and audit event
6. targeted tests
7. focused blast-radius tests

Do not start from UI.
Do not start from report export.
Do not reuse customer_refunds.
