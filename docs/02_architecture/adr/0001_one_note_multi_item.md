# ADR-0001 - One Note Multi-Item Model

- Status: Accepted
- Date: 2026-03-09
- Deciders: Project Owner, Architecture Decision
- Scope: Core Domain / Note / Payment / Inventory / Audit

## Context

The workshop domain being built is not a normal retail cashier model. In real operations, one customer may arrive with several needs in the same visit, for example:

- 1 product / service using store spare parts
- 1 product / service using customer-owned spare parts
- 1 product / service with no spare parts
- 1 product / service using spare parts bought from outside

The locked business requirements are:

- admin / cashier must not be forced to change their habit into creating multiple separate notes just because the items or statuses are different
- user input must remain simple: 1 customer can have 1 note containing various transaction / case lines
- item statuses inside a note may be different
- one customer may still have more than one active note at the same time if there really are other cases
- the system must remain accurate for partial payments, audit, stock, and reports

If the system forces one item or one case into a separate note, then it has failed to solve the client’s operational problem.

## Decision

The system uses the following model:

- **1 Note as the aggregate root for the transaction / customer-facing flow**
- **1 Note may contain many Work Items**
- **each Work Item may have different statuses, cost components, and spare-part sources**
- **payment is recorded at the Note level, with possible allocation to Work Items or to the Note total balance through policy / use case**
- **audit and correction are still tracked on the Note and the related item references**

## Decision Details

### 1. Conceptual structure

One Note must at minimum contain:

- note number
- customer reference
- created by
- created at
- note status
- list of work items
- payment records
- correction records
- audit references
- totals

One Work Item must at minimum contain:

- item identifier
- description of the unit / item / service object
- item status
- service lines
- store-stock part lines
- customer-owned part lines
- external purchase cost lines
- item subtotal
- item notes

### 2. Spare-part source per item

Every Work Item may contain lines with these sources:

- `store_stock`
- `customer_owned`
- `external_purchase`

The difference between these sources must exist in the core because it affects:

- stock
- case cost
- margin
- audit
- reporting

### 3. Status

Note status and Work Item status are separate.

Reason:

- one note can contain items with different progress
- one item may be complete while another is still pending
- the user still sees one operational note, while the system can track process granularity

### 4. Payment model

Payment is recorded against the Note because the operational user experience is centered on the note.

However, the design must keep open the possibility that:

- payment only reduces the Note’s total balance
- payment is allocated to specific items
- partial payment happens in stages

The exact allocation policy is decided in the use case / policy, not by changing the basic Note multi-item model.

## Alternatives Considered

### Alternative A - One item / one case = one note

Rejected.

Reasons:

- conflicts with the client’s operational habit
- makes admin / cashier work harder
- forces the user to change how the shop works
- creates more notes without business value
- makes the input experience unnatural

### Alternative B - The system silently creates many notes in the background

Rejected.

Reasons:

- increases synchronization complexity
- may break the user’s expectation of one note
- makes audit, correction, and payment tracking harder
- increases the risk of report mismatch

### Alternative C - One note with one flat line list and no Work Item concept

Rejected.

Reasons:

- not strong enough to model multiple cases / statuses in one note
- hard to manage item-level status
- hard to carry into precise audit and correction flows

## Consequences

### Positive

- matches the user’s real-world habit
- one note remains the center of the customer-facing interaction
- item statuses can be separated cleanly
- suitable for partial payments
- suitable for audit and correction
- more flexible for workshop operational reports

### Negative

- the domain model is more complex than a normal retail POS
- total and status calculations must be stricter
- the UI must stay simple even though the internal model is richer
- payment allocation must be designed carefully

## Invariants

- one Note may contain many Work Items
- one Work Item belongs to only one Note
- one customer may have more than one active Note
- the user is not forced to create many notes for one visit just because items or statuses differ
- changes to a paid Note may not be free and must go through an audited correction flow
- all money totals are calculated from official lines and stored in integer rupiah

## Implementation Notes

- Note is the main aggregate root for create, add item, total calculation, payment recording, correction, and audit reference operations
- Work Item may not become a separate aggregate that turns the user experience into multi-note behavior
- item status and note status must be separated from the beginning
- reports may read summaries per Note and per Work Item
- UI / HTTP / Telegram adapters may not change this base model

## Related Decisions

- ADR-002 - Negative Stock Policy Default Off
- ADR-003 - External Spare Part as Case Cost
- ADR-005 - Paid Note Correction Requires Audit
- ADR-009 - Reporting as Read Model
