# ADR-0005 - Paid Note Correction Requires Audit

- Status: SUPERSEDED IN PART by ADR-0016
- Date: 2026-03-09
- Deciders: Project Owner, Architecture Decision
- Scope: Core Domain / Note / Payment / Audit / Reporting / Authorization

## Supersession Note

ADR-0005 is superseded in part by ADR-0016.

Still valid from ADR-0005:

- a paid note mutation may not be a silent overwrite
- a reason is required
- an actor is required
- a timestamp is required
- a before / after snapshot is required
- sensitive mutation must be auditable
- reporting must be able to reconstruct the change history

Changed by ADR-0016:

- a closed / paid / refunded note is no longer treated as a terminal mutation lock
- adding a line after paid / refund can be valid when it happens through an audited revision / event flow
- refund can neutralize selected rows in the same note
- refund / cancel of an unpaid row can still reverse inventory and reduce outstanding
- post-consequence note mutation is allowed if financial ledger, inventory ledger, audit timeline, and reporting projection remain consistent

## Context

The transaction domain in this workshop system is highly sensitive because:

- payments may be partial
- one note may contain many work items
- reports and balances may not differ by 1 rupiah
- changes to a fully paid transaction can damage audit, reports, and the operational trace

The locked business requirements are:

- a fully paid note may **not** be freely changed
- if the goal is only to add a new item after the transaction is done / paid, it is better to create a new transaction / case
- if there is a wrong input, correction is still **allowed**
- the user only needs to fill in a **reason**
- the system must automatically record:
  - who
  - when
  - what changed before / after

The business therefore does not choose a total immutable model, and it also does not choose a free-edit model. The choice is:

- editable only through controlled correction with full audit

## Decision

The system sets:

- **a paid Note may not be freely edited**
- **every change to a paid Note may only happen through a controlled correction flow**
- **a correction must include a user-entered reason**
- **the system must automatically store the actor, timestamp, before snapshot, after snapshot, and change reference**
- **adding a new item after a paid Note must be created as a new transaction / case, not as a new line on the same paid Note**

## Decision Details

### 1. Boundary of “paid”

For this domain, a Note is considered paid when the official outstanding amount calculated by the domain is zero.

The paid status must come from:

- note total
- valid payment total
- official allocation / outstanding rule

Not from a manual UI toggle.

### 2. Allowed behavior after paid

After a Note is paid:

- free editing is forbidden
- adding a new item to the same Note is forbidden
- correction is allowed only through the official use case
- refund or adjustment may be used if the detailed implementation needs it
- every change must leave a full audit trail

### 3. Required user input

The user only needs to enter:

- correction reason

The following data may not be required as manual input:

- actor
- timestamp
- before snapshot
- after snapshot

All of those data must be generated and stored automatically by the system.

### 4. Why a new item becomes a new transaction

If after a Note is paid the user later wants to add more work or a new item, the official behavior is not to edit the old Note, but to:

- create a new transaction / case

Reasons:

- preserve the history of the transaction that has already been completed
- avoid mixing correction with new business work
- keep reports traceable

### 5. Audit scope

The minimum correction audit must capture:

- note reference
- actor
- timestamp
- reason
- before state
- after state
- affected fields or structured diff
- relation to payment / refund / correction event if any

## Alternatives Considered

### Alternative A - A paid note is fully immutable with no exceptions

Rejected.

Reasons:

- not aligned with the real business need
- the business still needs a way to fix wrong input
- it would push people to work around the system outside of the official flow

### Alternative B - A paid note may be edited freely if the user is careful

Rejected.

Reasons:

- conflicts with the business requirement
- destroys the audit trail
- creates a high risk for reports and reconciliation
- not suitable for a domain with 1-rupiah exactness sensitivity

### Alternative C - A paid note may be edited freely as long as there is a simple text log

Rejected.

Reasons:

- not strong enough
- does not guarantee before / after snapshots
- hard to do forensics on the change
- too weak for a sensitive finance domain

## Consequences

### Positive

- wrong input can still be corrected officially
- the history of paid transactions stays intact
- audit becomes stronger
- reports become more trustworthy
- the difference between correction and new business work stays clear

### Negative

- the correction flow is more complex than a simple edit
- snapshot / diff implementation requires discipline
- developers cannot use a plain CRUD update for a paid Note
- the UI must distinguish normal edit from correction

## Invariants

- a paid Note may not accept a new item
- correction on a paid Note must include a reason
- the actor and timestamp of correction must be recorded automatically
- before / after snapshots must exist for sensitive changes
- the official total / outstanding must still be reconstructable after a correction event
- a paid Note changed outside the correction flow is invalid at the domain level

## Implementation Notes

- recommended use cases:
  - `CorrectPaidNote`
  - `RecordRefund`
  - `RecordAdjustment`
- recommended domain errors:
  - `NOTE_ALREADY_PAID`
  - `NOTE_NEW_ITEMS_NOT_ALLOWED_AFTER_PAID`
  - `AUDIT_REASON_REQUIRED`
- the correction flow may not be replaced by a direct repository update
- reporting must be able to recognize that the change came from an official correction event
- the audit design may use full snapshots, structured diffs, or both, as long as the before / after state can be defended

## Related Decisions

- ADR-001 - One Note Multi-Item Model
- ADR-004 - Minimum Selling Price Guard
- ADR-008 - Audit-First Sensitive Mutations
- ADR-009 - Reporting as Read Model
- ADR-011 - Money Stored as Integer Rupiah
