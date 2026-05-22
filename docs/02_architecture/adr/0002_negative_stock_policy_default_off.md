# ADR-0002 - Negative Stock Policy Default Off

- Status: Accepted
- Date: 2026-03-09
- Deciders: Project Owner, Architecture Decision
- Scope: Core Domain / Inventory / Note / Supplier / Reporting

## Context

This system manages spare-part stock for workshop operations. From the locked business requirements:

- negative stock is not healthy for operations or finance
- negative stock must be forbidden by default
- however, the hexagonal structure must still make it easy to change later if the business ever wants a different behavior, such as backorder or a special policy

The system also has other locked rules:

- normal stock increases only come from valid supplier receipts
- stock decreases because of spare-part sales, spare-part usage for service, or official adjustments
- supplier invoices may not create a new product if the product master does not already exist
- reports and finance may not differ by even 1 rupiah

If negative stock is allowed by default:

- the inventory report becomes unhealthy
- cost and profit calculations become untrustworthy
- the system may record sales or stock usage for items that do not actually exist

## Decision

The system sets the default policy as follows:

- **negative stock is forbidden by default**
- **the negative-stock rule is enforced in the core inventory domain**
- **the rule is implemented as a policy / strategy that can be replaced later without changing the core structure**

## Decision Details

### 1. Default business rule

For every use case that reduces store inventory stock, the system must reject the operation if the outgoing quantity makes the balance drop below zero.

At minimum, the affected use cases are:

- store spare-part usage for a work item / note
- direct spare-part sales
- negative stock adjustments
- corrections that reduce stock

### 2. Domain location

The negative-stock rule must live in the core domain / application policy, not in:

- UI
- controller
- raw database query
- frontend validation

Reason:

- this is a core business rule
- it must be consistent across adapters
- it must remain correct if the future entry point becomes HTTP, CLI, Telegram, or another adapter

### 3. Extensibility

Even though the default is negative stock forbidden, the design must still leave room for extension points such as:

- `NegativeStockPolicy`
- `InventoryAvailabilityPolicy`

That way, if the business changes later, the implementation can be replaced without changing the architecture direction.

But until a new ADR changes it, the official behavior remains:

- negative stock not allowed

## Alternatives Considered

### Alternative A - Allow negative stock from the start

Rejected.

Reasons:

- directly conflicts with the business requirement
- damages operational and report health
- makes real stock control in the workshop difficult
- risks making sales or usage look valid when the item is actually unavailable

### Alternative B - Enforce negative stock only in the UI

Rejected.

Reasons:

- easy to bypass through other adapters
- does not guarantee consistency across entry points
- is not the correct place for the rule in hexagonal architecture

### Alternative C - Enforce negative stock only with a database constraint

Rejected as the main solution.

Reasons:

- database constraints are useful as an extra guard, but are not enough to express the business decision
- the resulting error is usually not domain-friendly
- it is harder to keep the behavior consistent at the use-case and audit levels

## Consequences

### Positive

- stock control stays healthier
- stock and operational reports are more trustworthy
- helps keep COGS and margin accurate
- prevents transactions that are not backed by stock availability
- matches the locked business requirement

### Negative

- some field transactions will fail faster and need proper operational handling
- correction / reversal logic must be handled carefully if stock already moved
- inventory testing becomes stricter

## Invariants

- store inventory stock may not fall below zero
- all stock decreases must go through official movements
- all normal stock increases must come from a valid path
- external purchase cost must not use the store inventory path
- customer-owned parts must not affect store inventory

## Implementation Notes

- stock-availability calculation must be based on balances that can be reconstructed from official movements
- stock validation must run before committing a mutating operation that reduces stock
- recommended domain errors:
  - `INVENTORY_INSUFFICIENT_STOCK`
  - `INVENTORY_NEGATIVE_STOCK_NOT_ALLOWED`
- the database constraint may be used as an extra guard, but not as the main source of the business rule
- reports may assume that negative balances are invalid at the domain level

## Related Decisions

- ADR-001 - One Note Multi-Item Model
- ADR-003 - External Spare Part as Case Cost
- ADR-006 - Costing Strategy Default Average, FIFO-ready
- ADR-011 - Money Stored as Integer Rupiah
- ADR-012 - Product Master Must Exist Before Supplier Receipt
