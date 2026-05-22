# ADR-0004 - Minimum Selling Price Guard

- Status: Accepted
- Date: 2026-03-09
- Deciders: Project Owner, Architecture Decision
- Scope: Core Domain / Catalog / Note / Payment / Authorization / Audit

## Context

This system manages spare-part and service transactions in a workshop domain with very high financial sensitivity. The locked business requirements are:

- the official product sale price is set in the product master
- the transaction sale price may be changed per case
- the transaction sale price may **not** be lower than the official / default minimum selling price
- if the customer ultimately pays less in real life, that may not corrupt the system’s official price
- the system may not legalize a reduction below the minimum price without an explicit new policy

If this guard does not exist:

- product margin becomes uncontrolled
- sales and profit reports become misleading
- a cashier can lower the official price without control
- price audit becomes weak
- the line between price policy and field improvisation becomes blurred

This domain also already locks in that:

- all money amounts use integer rupiah
- partial payment is a core feature
- correction for sensitive transactions must be audited
- reports read the final domain data, not a new source of truth

## Decision

The system sets:

- **every product has a minimum / default selling price that acts as the official lower bound**
- **the line selling price in a transaction / note may be overridden per case**
- **the price override is only valid if it is not lower than the applicable minimum / default price**
- **the core domain must reject a transaction line that goes below the minimum bound**
- **the guard runs in the domain / application policy, not only in the UI**

## Decision Details

### 1. Official price boundary

For every transaction that uses a store-stock product, the system must compare the proposed line price with the official minimum selling price.

If:

- `transaction_line_price < minimum_selling_price`

then the operation must be rejected.

### 2. Scope of enforcement

This guard must at minimum apply to:

- direct spare-part sales
- store spare-part usage on a work item / note
- line-price corrections involving official products
- price changes on transactions that are still editable according to the domain

### 3. Domain placement

The minimum price guard must live in the core domain / application policy.

It may not depend on:

- frontend validation
- controller code
- JavaScript UI
- ad hoc database queries

Reason:

- this is a core business rule
- it must remain consistent across adapters
- it must still be correct if transactions later come from HTTP, CLI, import, or another adapter

### 4. Boundary with real-world underpayment

The business requirement currently only locks that the official price may not go below the minimum bound.

That means:

- the system may **not** record an official line price below the minimum
- if in the field a customer pays less and the difference is covered privately by the cashier, that behavior is **not** part of this minimum-price guard
- if the business later needs an official mechanism for:
  - internal subsidy
  - write-off
  - approved discount exception
  - a cashier covering the difference through an official flow

  then that must be decided in a separate ADR so price integrity and cash-handling exceptions are not mixed

Until there is a new decision, the official system behavior is:

- official selling price may be higher than or equal to the floor
- official selling price may not be below the floor

## Alternatives Considered

### Alternative A - Any transaction price is allowed as long as the total payment matches

Rejected.

Reasons:

- conflicts with the business need
- destroys margin control
- opens the door to manipulating the official price
- weakens operational audit

### Alternative B - Guard only in the UI

Rejected.

Reasons:

- easy to bypass through other entry points
- not the correct place for the rule in hexagonal architecture
- does not guarantee consistency across all adapters

### Alternative C - Hard-code the minimum price in a controller or query

Rejected.

Reasons:

- not portable
- hard to maintain
- breaks separation of concerns
- makes framework / language migration harder

### Alternative D - Allow prices below the minimum and call them normal discounts

Rejected.

Reasons:

- no business need currently allows that
- distorts the meaning of the official price
- hides an exception inside normal behavior

## Consequences

### Positive

- the official price integrity is preserved
- the minimum margin is more controlled
- spare-part sales reports are more trustworthy
- transaction audit is healthier
- the behavior matches the locked business requirement

### Negative

- field transactions that want to be cheaper will fail earlier and need proper handling
- if the business wants an official exception flow in the future, a new decision is needed
- correction and price-change implementations must call this policy consistently

## Invariants

- every store-stock product has a reference minimum / default selling price
- the official transaction line price for an official product may not be below the floor
- the guard must be consistent across all adapters
- customer-owned parts are not governed by the store-stock product price guard
- external purchase cost lines are not subject to the minimum selling price product guard
- sensitive price changes must still be auditable when relevant

## Implementation Notes

- recommended domain service / policy:
  - `MinSellingPricePolicy`
  - `SellingPriceGuard`
- recommended domain error:
  - `PRICING_BELOW_MINIMUM_SELLING_PRICE`
- the catalog must be the source of the official minimum price
- the policy must consider the effective product price state that applies when the transaction is created or corrected, according to the later implementation details
- if there is ever an approval-based override, that must be a separate decision and must not change this default invariant

## Related Decisions

- ADR-001 - One Note Multi-Item Model
- ADR-005 - Paid Note Correction Requires Audit
- ADR-006 - Costing Strategy Default Average, FIFO-ready
- ADR-008 - Audit-First Sensitive Mutations
- ADR-011 - Money Stored as Integer Rupiah
