# ADR-0003 - External Spare Part as Case Cost

- Status: Accepted
- Date: 2026-03-09
- Deciders: Project Owner, Architecture Decision
- Scope: Core Domain / Note / Payment / Reporting / Inventory

## Context

In this workshop, there are real cases where the spare part for a customer job is not taken from store inventory, but bought from outside to satisfy a specific case.

The locked business requirements are:

- externally bought spare parts do not enter store product inventory
- the healthiest record is to treat them as case cost
- the main reason: the user needs a clear view of why the net result of a service is a certain amount
- external part cost may not be hidden as pure service profit because that would confuse audit and reports
- a concrete domain example:
  - charge to customer: 90,000
  - outside part cost: 30,000
  - operational case net result: 60,000

The system already locks in that one note may contain several work items with different spare-part sources.

## Decision

The system sets:

- **spare parts bought from outside are recorded as case / work-item cost**
- **external spare parts do not add to store inventory stock**
- **external spare parts are not treated as a product inventory receipt**
- **margin / net result calculations must be able to read this external cost separately**

## Decision Details

### 1. Domain placement

External spare-part cost becomes part of the Note / Work Item domain, not the Inventory domain.

Conceptually, this line is treated as:

- `external_purchase_cost_line`
- or another clear name that shows it is an external cost used to complete the item / case

### 2. Financial effect

When an external purchase cost line is recorded:

- the customer charge still follows the official billed lines
- the external cost is recorded as a cost component for the item / case
- store inventory does not change
- operational reports can calculate the item / case margin correctly

### 3. Reporting effect

Because the external cost is recorded explicitly, reports can distinguish:

- service and / or item revenue
- external part cost
- operational net result for the case

This matters so that people do not end up asking:

- why does the service look too small
- why is the case margin unclear
- why can the report not be traced back to the source

### 4. Boundary with inventory

External purchase cost may not be treated as:

- product receipt
- stock purchase
- on-hand inventory
- store-stock part usage

If the business ever wants a different scenario, such as a part first entering inventory and then leaving again, that requires a new decision. Until then, the official model remains:

- external purchase is case cost, not inventory

## Alternatives Considered

### Alternative A - External purchase is treated directly as part of pure service price

Rejected.

Reasons:

- hides the real cost
- may confuse the user during audit
- makes margin harder to explain
- weakens cost and operational analysis

### Alternative B - External purchase is forced into store inventory

Rejected for the default domain.

Reasons:

- does not match the locked business need
- adds unnecessary operational steps
- misrepresents the reality when the item was only bought for a specific case
- adds noise to inventory movement

### Alternative C - External purchase is recorded outside the Note entirely

Rejected.

Reasons:

- breaks the connection between the cost and its source case
- makes case-margin tracking harder
- weakens audit and reporting

## Consequences

### Positive

- case cost is recorded explicitly
- item / case margin becomes more transparent
- inventory stays clean from items that never actually became store stock
- matches the shop’s real-world process
- helps with audit and reports

### Negative

- the financial line model in Note / Work Item becomes richer
- reports must distinguish revenue and external cost carefully
- the UI must still stay simple even though the internal structure is more detailed

## Invariants

- external purchase cost does not change store inventory balance
- external purchase cost must be tied to the relevant note / work item
- external cost values must be recorded explicitly
- item / case margin must be traceable from raw data
- the external purchase line may not be processed by the store-stock costing strategy

## Implementation Notes

- the work item must support a special line for external cost
- line naming must be clear and not ambiguous with service revenue or store-stock part lines
- reports should at minimum be able to show:
  - note / item revenue
  - external purchase cost
  - operational net result
- important guards:
  - external purchase line may not reduce stock
  - external purchase line may not be treated as a product receipt
- if a future supplier payable model is needed for this type of outside purchase, that decision must be discussed separately so procurement stock and case cost are not mixed

## Related Decisions

- ADR-001 - One Note Multi-Item Model
- ADR-002 - Negative Stock Policy Default Off
- ADR-005 - Paid Note Correction Requires Audit
- ADR-006 - Costing Strategy Default Average, FIFO-ready
- ADR-009 - Reporting as Read Model
